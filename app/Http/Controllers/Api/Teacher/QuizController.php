<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionMatchingPair;
use App\Models\StudentEnrollment;
use App\Models\Quiz;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuizController extends Controller
{
    /**
     * Get this teacher's quizzes.
     */
    public function index(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            return response()->json(['data' => []]);
        }

        $quizzes = Quiz::query()
            ->where('teacher_profile_id', $teacher->id)
            ->with(['subject', 'classroom'])
            ->withCount('submissions')
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->input('subject_id'), fn ($query, $id) => $query->where('subject_id', $id))
            ->when($request->input('class_id'), fn ($query, $id) => $query->where('class_id', $id))
            ->when($request->input('q'), fn ($query, $q) => $query->where('title', 'like', "%{$q}%"))
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $quizzes->map(fn (Quiz $quiz) => $this->transform($quiz)),
        ]);
    }

    /**
     * Get one quiz with its ordered questions, for the builder/preview.
     */
    public function show(Request $request, Quiz $quiz)
    {
        $teacher = $request->user()->teacherProfile;
        $this->authorizeOwner($teacher, $quiz);

        $quiz->load('subject', 'classroom')->loadCount('submissions');

        return response()->json([
            'quiz' => array_merge($this->transform($quiz), [
                'questions' => $this->transformQuestions($quiz),
            ]),
        ]);
    }

    /**
     * Create a new quiz (always starts as a Draft) with an explicit set of questions.
     */
    public function store(Request $request)
    {
        $teacher = $request->user()->teacherProfile;
        $validated = $this->validated($request, $teacher->id);

        $quiz = DB::transaction(function () use ($validated, $teacher) {
            $quiz = Quiz::create([
                'teacher_profile_id' => $teacher->id,
                'subject_id' => $validated['subject_id'],
                'class_id' => $validated['class_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'duration_minutes' => $validated['duration_minutes'],
                'max_attempts' => $validated['max_attempts'],
                'shuffle_questions' => $validated['shuffle_questions'] ?? false,
                'shuffle_options' => $validated['shuffle_options'] ?? false,
                'pass_mark' => $validated['pass_mark'] ?? null,
                'start_at' => $validated['start_at'] ?? null,
                'end_at' => $validated['end_at'] ?? null,
                'total_questions' => 0,
                'status' => 'Draft',
            ]);

            $this->syncQuestions($quiz, $validated['question_ids'] ?? []);

            return $quiz;
        });

        $quiz->load('subject', 'classroom')->loadCount('submissions');

        return response()->json([
            'message' => 'Quiz created successfully.',
            'quiz' => $this->transform($quiz),
        ], 201);
    }

    /**
     * Update a quiz's metadata and question selection. Status is not editable here
     * (see publish()/close()) so an in-review Published quiz can't silently revert.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $teacher = $request->user()->teacherProfile;
        $this->authorizeOwner($teacher, $quiz);

        $validated = $this->validated($request, $teacher->id);

        DB::transaction(function () use ($validated, $quiz) {
            $quiz->update([
                'subject_id' => $validated['subject_id'],
                'class_id' => $validated['class_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'duration_minutes' => $validated['duration_minutes'],
                'max_attempts' => $validated['max_attempts'],
                'shuffle_questions' => $validated['shuffle_questions'] ?? false,
                'shuffle_options' => $validated['shuffle_options'] ?? false,
                'pass_mark' => $validated['pass_mark'] ?? null,
                'start_at' => $validated['start_at'] ?? null,
                'end_at' => $validated['end_at'] ?? null,
            ]);

            $this->syncQuestions($quiz, $validated['question_ids'] ?? []);
        });

        $quiz->load('subject', 'classroom')->loadCount('submissions');

        return response()->json([
            'message' => 'Quiz updated successfully.',
            'quiz' => $this->transform($quiz),
        ]);
    }

    /**
     * Publish a quiz so students can see/attempt it within its availability window.
     */
    public function publish(Request $request, Quiz $quiz)
    {
        $teacher = $request->user()->teacherProfile;
        $this->authorizeOwner($teacher, $quiz);

        if ($quiz->total_questions < 1) {
            throw ValidationException::withMessages([
                'questions' => ['Add at least one question before publishing.'],
            ]);
        }

        $quiz->update(['status' => 'Published']);
        $quiz->load('subject', 'classroom')->loadCount('submissions');

        return response()->json([
            'message' => 'Quiz published.',
            'quiz' => $this->transform($quiz),
        ]);
    }

    /**
     * Close a quiz (manually end it, or after its window has passed).
     */
    public function close(Request $request, Quiz $quiz)
    {
        $teacher = $request->user()->teacherProfile;
        $this->authorizeOwner($teacher, $quiz);

        $quiz->update(['status' => 'Closed']);
        $quiz->load('subject', 'classroom')->loadCount('submissions');

        return response()->json([
            'message' => 'Quiz closed.',
            'quiz' => $this->transform($quiz),
        ]);
    }

    /**
     * Delete a quiz.
     */
    public function destroy(Request $request, Quiz $quiz)
    {
        $teacher = $request->user()->teacherProfile;
        $this->authorizeOwner($teacher, $quiz);

        $quiz->delete();

        return response()->json(['message' => 'Quiz deleted successfully.']);
    }

    private function authorizeOwner(?object $teacher, Quiz $quiz): void
    {
        if (! $teacher || $quiz->teacher_profile_id !== $teacher->id) {
            abort(403);
        }
    }

    private function validated(Request $request, int $teacherId): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            'shuffle_questions' => ['boolean'],
            'shuffle_options' => ['boolean'],
            'pass_mark' => ['nullable', 'integer', 'min:0', 'max:100'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after:start_at'],
            'question_ids' => ['array'],
            'question_ids.*' => ['integer'],
        ]);

        $isAssigned = TeacherSubject::where('teacher_profile_id', $teacherId)
            ->where('subject_id', $validated['subject_id'])
            ->where('class_id', $validated['class_id'])
            ->exists();

        if (! $isAssigned) {
            throw ValidationException::withMessages([
                'class_id' => ['You are not assigned to teach this subject for this class.'],
            ]);
        }

        if (! empty($validated['question_ids'])) {
            $validCount = Question::where('teacher_profile_id', $teacherId)
                ->where('subject_id', $validated['subject_id'])
                ->whereIn('id', $validated['question_ids'])
                ->count();

            if ($validCount !== count(array_unique($validated['question_ids']))) {
                throw ValidationException::withMessages([
                    'question_ids' => ['One or more selected questions are invalid for this subject.'],
                ]);
            }
        }

        return $validated;
    }

    private function syncQuestions(Quiz $quiz, array $questionIds): void
    {
        $questionIds = array_values(array_unique($questionIds));

        $quiz->questions()->sync(
            collect($questionIds)->mapWithKeys(fn ($id, $position) => [$id => ['position' => $position]])
        );

        $quiz->update(['total_questions' => count($questionIds)]);
    }

    private function transformQuestions(Quiz $quiz): array
    {
        return $quiz->orderedQuestions()
            ->with('options', 'matchingPairs')
            ->get()
            ->map(fn (Question $question) => [
                'id' => $question->id,
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
            ])
            ->values()
            ->all();
    }

    private function transform(Quiz $quiz): array
    {
        $totalStudents = StudentEnrollment::where('class_id', $quiz->class_id)
            ->where('status', 'Active')
            ->count();

        return [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'subject_id' => $quiz->subject_id,
            'subjectCode' => $quiz->subject?->code,
            'class_id' => $quiz->class_id,
            'className' => $quiz->classroom?->name,
            'duration' => $quiz->duration_minutes,
            'maxAttempts' => $quiz->max_attempts,
            'shuffleQuestions' => $quiz->shuffle_questions,
            'shuffleOptions' => $quiz->shuffle_options,
            'passMark' => $quiz->pass_mark,
            'startAt' => $quiz->start_at?->format('Y-m-d\TH:i'),
            'endAt' => $quiz->end_at?->format('Y-m-d\TH:i'),
            'totalQuestions' => $quiz->total_questions,
            'submittedCount' => $quiz->submissions_count ?? 0,
            'totalStudents' => $totalStudents,
            'status' => $quiz->status,
        ];
    }
}
