<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\SaveQuestionRequest;
use App\Models\Question;
use App\Models\QuestionMatchingPair;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            ->with(['subject', 'options', 'matchingPairs'])
            ->when($request->input('subject_id'), fn ($query, $id) => $query->where('subject_id', $id))
            ->when($request->input('type'), fn ($query, $type) => $query->where('type', $type))
            ->when($request->input('q'), fn ($query, $q) => $query->where('title', 'like', "%{$q}%"))
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $questions->map(fn (Question $question) => $this->transform($question)),
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

        $question->load('subject', 'options', 'matchingPairs');

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

        $question->load('subject', 'options', 'matchingPairs');

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
