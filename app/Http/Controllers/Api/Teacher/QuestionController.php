<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionMatchingPair;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    public function store(Request $request)
    {
        $teacher = $request->user()->teacherProfile;
        $validated = $this->validated($request);

        $question = DB::transaction(function () use ($validated, $teacher) {
            $question = Question::create([
                'teacher_profile_id' => $teacher->id,
                'subject_id' => $validated['subject_id'],
                'type' => $validated['type'],
                'title' => $validated['title'],
                'difficulty' => $validated['difficulty'],
                'sample_answer' => $validated['sample_answer'] ?? null,
            ]);

            $this->syncOptions($question, $validated);

            return $question;
        });

        $question->load('subject', 'options', 'matchingPairs');

        return response()->json([
            'message' => 'Question created successfully.',
            'question' => $this->transform($question),
        ], 201);
    }

    /**
     * Update a question.
     */
    public function update(Request $request, Question $question)
    {
        $this->authorizeOwner($request, $question);

        $validated = $this->validated($request);

        DB::transaction(function () use ($validated, $question) {
            $question->update([
                'subject_id' => $validated['subject_id'],
                'type' => $validated['type'],
                'title' => $validated['title'],
                'difficulty' => $validated['difficulty'],
                'sample_answer' => $validated['sample_answer'] ?? null,
            ]);

            $this->syncOptions($question, $validated);
        });

        $question->load('subject', 'options', 'matchingPairs');

        return response()->json([
            'message' => 'Question updated successfully.',
            'question' => $this->transform($question),
        ]);
    }

    /**
     * Delete a question.
     */
    public function destroy(Request $request, Question $question)
    {
        $this->authorizeOwner($request, $question);

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

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'type' => ['required', Rule::in(['multiple_choice', 'true_false', 'essay', 'matching'])],
            'difficulty' => ['required', Rule::in(['Easy', 'Medium', 'Hard'])],
            'title' => ['required', 'string'],
            'sample_answer' => ['nullable', 'string'],
            'options' => ['required_if:type,multiple_choice', 'array'],
            'options.*.text' => ['nullable', 'string'],
            'options.*.isCorrect' => ['boolean'],
            'tfCorrect' => ['required_if:type,true_false', Rule::in(['True', 'False'])],
            'matchingPairs' => ['required_if:type,matching', 'array'],
            'matchingPairs.*.leftText' => ['nullable', 'string'],
            'matchingPairs.*.rightText' => ['nullable', 'string'],
        ]);

        if ($validated['type'] === 'multiple_choice') {
            $filled = collect($validated['options'] ?? [])->filter(fn ($option) => trim($option['text'] ?? '') !== '');

            if ($filled->count() < 2) {
                throw ValidationException::withMessages([
                    'options' => ['A multiple choice question needs at least 2 answer options.'],
                ]);
            }

            if ($filled->filter(fn ($option) => (bool) ($option['isCorrect'] ?? false))->count() < 1) {
                throw ValidationException::withMessages([
                    'options' => ['At least one answer option must be marked as correct.'],
                ]);
            }
        }

        if ($validated['type'] === 'matching') {
            $filled = collect($validated['matchingPairs'] ?? [])->filter(
                fn ($pair) => trim($pair['leftText'] ?? '') !== '' && trim($pair['rightText'] ?? '') !== ''
            );

            if ($filled->count() < 2) {
                throw ValidationException::withMessages([
                    'matchingPairs' => ['A matching question needs at least 2 complete pairs.'],
                ]);
            }
        }

        return $validated;
    }

    private function syncOptions(Question $question, array $validated): void
    {
        $question->options()->delete();
        $question->matchingPairs()->delete();

        if ($validated['type'] === 'multiple_choice') {
            foreach ($validated['options'] as $index => $option) {
                if (trim($option['text'] ?? '') === '') {
                    continue;
                }

                QuestionOption::create([
                    'question_id' => $question->id,
                    'text' => $option['text'],
                    'is_correct' => (bool) ($option['isCorrect'] ?? false),
                    'position' => $index,
                ]);
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

            foreach ($validated['matchingPairs'] as $pair) {
                if (trim($pair['leftText'] ?? '') === '' || trim($pair['rightText'] ?? '') === '') {
                    continue;
                }

                QuestionMatchingPair::create([
                    'question_id' => $question->id,
                    'left_text' => $pair['leftText'],
                    'right_text' => $pair['rightText'],
                    'position' => $position++,
                ]);
            }
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
            'title' => $question->title,
            'sampleAnswer' => $question->sample_answer,
            'options' => $question->options->sortBy('position')->values()->map(fn (QuestionOption $option) => [
                'id' => $option->id,
                'text' => $option->text,
                'isCorrect' => $option->is_correct,
            ]),
            'matchingPairs' => $question->matchingPairs->sortBy('position')->values()->map(fn (QuestionMatchingPair $pair) => [
                'id' => $pair->id,
                'leftText' => $pair->left_text,
                'rightText' => $pair->right_text,
            ]),
        ];
    }
}
