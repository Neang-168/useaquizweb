<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\SaveQuestionRequest;
use App\Models\Llo;
use App\Models\Question;
use App\Models\QuestionMatchingPair;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    /**
     * Get this teacher's question bank.
     */
    public function index(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            return response()->json(['data' => []]);
        }

        $questions = Question::query()
            ->where('teacher_profile_id', $teacher->id)
            ->with(['subject', 'llo', 'options', 'matchingPairs'])
            ->when($request->input('subject_id'), fn ($query, $id) => $query->where('subject_id', $id))
            ->when($request->input('type'), fn ($query, $type) => $query->where('type', $type))
            ->when($request->input('difficulty'), fn ($query, $difficulty) => $query->where('difficulty', $difficulty))
            ->when($request->input('clo_id'), fn ($query, $id) => $query->whereHas('llo', fn ($q) => $q->where('clo_id', $id)))
            ->when($request->input('llo_id'), fn ($query, $id) => $query->where('llo_id', $id))
            ->when($request->input('q'), fn ($query, $q) => $query->where('title', 'like', "%{$q}%"))
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $questions->map(fn (Question $question) => $this->transform($question)),
        ]);
    }

    /**
     * Pick a random sample of this teacher's questions matching the given
     * filters, for building a quiz by outcome coverage rather than picking
     * every question by hand. Never errors on a thin bank — if fewer
     * questions match than requested, returns what exists plus a shortfall
     * count so the caller can tell the difference between "picked N" and
     * "wanted N but only M existed."
     */
    public function random(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            abort(403);
        }

        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'clo_id' => ['nullable', 'integer', 'exists:clos,id'],
            'llo_id' => ['nullable', 'integer', 'exists:llos,id'],
            'difficulty' => ['nullable', Rule::in(['Easy', 'Medium', 'Hard'])],
            'question_type' => ['nullable', Rule::in(['multiple_choice', 'true_false', 'matching'])],
            'count' => ['required', 'integer', 'min:1', 'max:200'],
            'exclude_ids' => ['nullable', 'array'],
            'exclude_ids.*' => ['integer'],
        ]);

        $questions = Question::query()
            ->where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $validated['subject_id'])
            ->with(['subject', 'llo', 'options', 'matchingPairs'])
            ->when($validated['clo_id'] ?? null, fn ($query, $id) => $query->whereHas('llo', fn ($q) => $q->where('clo_id', $id)))
            ->when($validated['llo_id'] ?? null, fn ($query, $id) => $query->where('llo_id', $id))
            ->when($validated['difficulty'] ?? null, fn ($query, $difficulty) => $query->where('difficulty', $difficulty))
            ->when($validated['question_type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when(! empty($validated['exclude_ids']), fn ($query) => $query->whereNotIn('id', $validated['exclude_ids']))
            ->inRandomOrder()
            ->limit($validated['count'])
            ->get();

        return response()->json([
            'data' => $questions->map(fn (Question $question) => $this->transform($question)),
            'shortfall' => max(0, $validated['count'] - $questions->count()),
        ]);
    }

    /**
     * This teacher's questions that have no LLO tag yet, for the bulk-tag
     * screen in Question Bank (e.g. questions created via import, which
     * never goes through SaveQuestionRequest).
     */
    public function untagged(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            return response()->json(['data' => []]);
        }

        $questions = Question::query()
            ->where('teacher_profile_id', $teacher->id)
            ->whereNull('llo_id')
            ->with(['subject', 'options', 'matchingPairs'])
            ->when($request->input('subject_id'), fn ($query, $id) => $query->where('subject_id', $id))
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $questions->map(fn (Question $question) => $this->transform($question)),
        ]);
    }

    /**
     * Tag many untagged questions with one LLO in a single action. Only
     * this teacher's own, currently-untagged questions whose subject
     * matches the LLO's subject are updated; anything else requested is
     * silently skipped and reported back, since attempting to tag another
     * subject's question with this LLO would violate the same subject
     * match SaveQuestionRequest enforces on the single-question path.
     */
    public function bulkTagLlo(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            abort(403);
        }

        $validated = $request->validate([
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:questions,id'],
            'llo_id' => ['required', 'exists:llos,id'],
        ]);

        $llo = Llo::with('clo')->findOrFail($validated['llo_id']);
        $subjectId = $llo->clo?->subject_id;

        $eligibleIds = Question::query()
            ->whereIn('id', $validated['question_ids'])
            ->where('teacher_profile_id', $teacher->id)
            ->where('subject_id', $subjectId)
            ->whereNull('llo_id')
            ->pluck('id');

        DB::transaction(function () use ($eligibleIds, $validated) {
            Question::whereIn('id', $eligibleIds)->update(['llo_id' => $validated['llo_id']]);
        });

        $skippedIds = array_values(array_diff($validated['question_ids'], $eligibleIds->all()));

        return response()->json([
            'message' => $eligibleIds->count().' question(s) tagged, '.count($skippedIds).' skipped.',
            'tagged' => $eligibleIds->count(),
            'skippedIds' => $skippedIds,
        ]);
    }

    /**
     * Create a new question.
     */
    public function store(SaveQuestionRequest $request)
    {
        $teacher = $request->user()->teacherProfile;
        $validated = $request->validated();

        [$question, $staleFiles] = DB::transaction(fn () => $this->persist($validated, $teacher->id, null));

        $this->deleteFiles($staleFiles);

        $question->load('subject', 'llo', 'options', 'matchingPairs');

        return response()->json([
            'message' => 'Question created successfully.',
            'question' => $this->transform($question),
        ], 201);
    }

    /**
     * Update a question.
     */
    public function update(SaveQuestionRequest $request, Question $question)
    {
        $this->authorizeOwner($request, $question);

        $validated = $request->validated();

        [$question, $staleFiles] = DB::transaction(fn () => $this->persist($validated, null, $question));

        $this->deleteFiles($staleFiles);

        $question->load('subject', 'llo', 'options', 'matchingPairs');

        return response()->json([
            'message' => 'Question updated successfully.',
            'question' => $this->transform($question),
        ]);
    }

    /**
     * Delete a question. Question::booted() takes care of deleting its
     * image files (stem, options, matching pairs) before the FK cascade
     * removes the child rows.
     *
     * Blocked while the question is attached to any quiz: quiz_questions
     * has question_id set to cascade-delete, so deleting an in-use question
     * would silently drop it out of every quiz that includes it (and leave
     * that quiz's total_questions count wrong) without the teacher ever
     * being told. Removing it from those quizzes first is what actually
     * needs to happen before it can be deleted.
     */
    public function destroy(Request $request, Question $question)
    {
        $this->authorizeOwner($request, $question);

        $quizTitles = $question->quizzes()->pluck('title');

        if ($quizTitles->isNotEmpty()) {
            $list = $quizTitles->take(5)->implode(', ').($quizTitles->count() > 5 ? ', …' : '');

            throw ValidationException::withMessages([
                'question' => ["This question is used in: {$list}. Remove it from those quizzes before deleting it."],
            ]);
        }

        $question->delete();

        return response()->json(['message' => 'Question deleted successfully.']);
    }

    private function authorizeOwner(Request $request, Question $question): void
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher || $question->teacher_profile_id !== $teacher->id) {
            abort(403);
        }
    }

    /**
     * Create or update a question plus its options/matching pairs, resolving
     * every image field's tri-state (unchanged / replaced / removed) against
     * whatever the question previously had. Returns the saved question and
     * the list of now-orphaned file paths (old files that were replaced or
     * removed, and freshly-uploaded files that ended up unused) for the
     * caller to delete once the transaction has actually committed.
     *
     * @return array{0: Question, 1: string[]}
     */
    private function persist(array $validated, ?int $teacherId, ?Question $question): array
    {
        $oldType = $question?->type;
        $oldOptions = $oldType === 'multiple_choice'
            ? $question->options()->orderBy('position')->get()
            : collect();
        $oldPairs = $oldType === 'matching'
            ? $question->matchingPairs()->orderBy('position')->get()
            : collect();

        $oldPaths = collect([$question?->image_path])
            ->merge($oldOptions->pluck('image_path'))
            ->merge($oldPairs->pluck('left_image_path'))
            ->merge($oldPairs->pluck('right_image_path'))
            ->filter()
            ->all();

        $movedPaths = [];
        $persistedPaths = [];

        // Moves a fresh tmp/ upload to its permanent home, or carries an
        // existing path forward untouched, or drops it (removed / never set).
        $resolveImage = function (?string $token, bool $remove, ?string $existingPath) use (&$movedPaths) {
            if ($token) {
                $path = 'questions/'.$token;
                Storage::disk('public')->move('tmp/'.$token, $path);
                $movedPaths[] = $path;

                return $path;
            }

            if (! $remove && $existingPath) {
                return $existingPath;
            }

            return null;
        };

        $imagePath = $resolveImage(
            $validated['image_token'] ?? null,
            (bool) ($validated['remove_image'] ?? false),
            $question?->image_path,
        );

        if ($imagePath) {
            $persistedPaths[] = $imagePath;
        }

        $attributes = [
            'subject_id' => $validated['subject_id'],
            'llo_id' => $validated['llo_id'],
            'type' => $validated['type'],
            'title' => filled($validated['title'] ?? null) ? $validated['title'] : null,
            'difficulty' => $validated['difficulty'],
            'points' => $validated['points'],
            'image_path' => $imagePath,
            'image_alt' => $validated['image_alt'] ?? null,
        ];

        if ($question) {
            $question->update($attributes);
        } else {
            $question = Question::create($attributes + ['teacher_profile_id' => $teacherId]);
        }

        $question->options()->delete();
        $question->matchingPairs()->delete();

        if ($validated['type'] === 'multiple_choice') {
            foreach ($validated['options'] as $index => $option) {
                $path = $resolveImage(
                    $option['image_token'] ?? null,
                    (bool) ($option['remove_image'] ?? false),
                    $oldOptions->get($index)?->image_path,
                );
                $text = trim($option['text'] ?? '') !== '' ? $option['text'] : null;

                if (! $text && ! $path) {
                    continue;
                }

                QuestionOption::create([
                    'question_id' => $question->id,
                    'text' => $text,
                    'image_path' => $path,
                    'is_correct' => (bool) ($option['isCorrect'] ?? false),
                    'position' => $index,
                ]);

                if ($path) {
                    $persistedPaths[] = $path;
                }
            }
        } elseif ($validated['type'] === 'true_false') {
            QuestionOption::create([
                'question_id' => $question->id,
                'text' => 'True',
                'is_correct' => $validated['tfCorrect'] === 'True',
                'position' => 0,
            ]);
            QuestionOption::create([
                'question_id' => $question->id,
                'text' => 'False',
                'is_correct' => $validated['tfCorrect'] === 'False',
                'position' => 1,
            ]);
        } elseif ($validated['type'] === 'matching') {
            $position = 0;

            foreach ($validated['matchingPairs'] as $index => $pair) {
                $oldRow = $oldPairs->get($index);

                $leftPath = $resolveImage(
                    $pair['leftImageToken'] ?? null,
                    (bool) ($pair['removeLeftImage'] ?? false),
                    $oldRow?->left_image_path,
                );
                $rightPath = $resolveImage(
                    $pair['rightImageToken'] ?? null,
                    (bool) ($pair['removeRightImage'] ?? false),
                    $oldRow?->right_image_path,
                );

                $leftText = trim($pair['leftText'] ?? '') !== '' ? $pair['leftText'] : null;
                $rightText = trim($pair['rightText'] ?? '') !== '' ? $pair['rightText'] : null;

                if ((! $leftText && ! $leftPath) || (! $rightText && ! $rightPath)) {
                    continue;
                }

                QuestionMatchingPair::create([
                    'question_id' => $question->id,
                    'left_text' => $leftText,
                    'left_image_path' => $leftPath,
                    'right_text' => $rightText,
                    'right_image_path' => $rightPath,
                    'position' => $position++,
                ]);

                if ($leftPath) {
                    $persistedPaths[] = $leftPath;
                }
                if ($rightPath) {
                    $persistedPaths[] = $rightPath;
                }
            }
        }

        $staleFiles = array_values(array_unique(array_merge(
            array_diff($oldPaths, $persistedPaths),
            array_diff($movedPaths, $persistedPaths),
        )));

        return [$question, $staleFiles];
    }

    private function deleteFiles(array $paths): void
    {
        if ($paths) {
            Storage::disk('public')->delete($paths);
        }
    }

    private function transform(Question $question): array
    {
        return [
            'id' => $question->id,
            'subject_id' => $question->subject_id,
            'subjectCode' => $question->subject?->code,
            'llo_id' => $question->llo_id,
            'lloTitle' => $question->llo?->title,
            'type' => $question->type,
            'difficulty' => $question->difficulty,
            'points' => $question->points,
            'title' => $question->title,
            'imageUrl' => $question->image_path ? Storage::disk('public')->url($question->image_path) : null,
            'imageAlt' => $question->image_alt,
            'options' => $question->options->sortBy('position')->values()->map(fn (QuestionOption $option) => [
                'id' => $option->id,
                'text' => $option->text,
                'isCorrect' => $option->is_correct,
                'imageUrl' => $option->image_path ? Storage::disk('public')->url($option->image_path) : null,
            ]),
            'matchingPairs' => $question->matchingPairs->sortBy('position')->values()->map(fn (QuestionMatchingPair $pair) => [
                'id' => $pair->id,
                'leftText' => $pair->left_text,
                'leftImageUrl' => $pair->left_image_path ? Storage::disk('public')->url($pair->left_image_path) : null,
                'rightText' => $pair->right_text,
                'rightImageUrl' => $pair->right_image_path ? Storage::disk('public')->url($pair->right_image_path) : null,
            ]),
        ];
    }
}
