<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionMatchingPair;
use App\Models\Quiz;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        // Self-heals expired quizzes to Closed even if the scheduler isn't
        // wired to a system cron — cheap, idempotent single UPDATE.
        Quiz::autoCloseExpired();

        $quizzes = Quiz::query()
            ->where('teacher_profile_id', $teacher->id)
            ->with(['subject', 'classroom'])
            ->withCount('submissions')
            ->with('questions')
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

        $quiz->load('subject', 'classroom')->loadCount('submissions')->load('questions');

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
                'total_score' => $validated['total_score'] ?? null,
                'start_at' => $validated['start_at'] ?? null,
                'end_at' => $validated['end_at'] ?? null,
                'total_questions' => 0,
                'status' => 'Draft',
            ]);

            $this->syncQuestions($quiz, $validated['question_ids'] ?? [], $validated['total_score'] ?? null);

            return $quiz;
        });

        $quiz->load('subject', 'classroom')->loadCount('submissions')->load('questions');

        return response()->json([
            'message' => 'Quiz created successfully.',
            'quiz' => $this->transform($quiz),
        ], 201);
    }

    /**
     * Update a quiz's metadata and question selection. Status is not editable here
     * (see publish()/close()) so an in-review Published quiz can't silently revert.
     *
     * A Draft quiz can always be edited. A Published quiz can still be edited as
     * long as its start window hasn't opened yet (no student could have started
     * an attempt) — once started, or once Closed, content and settings are
     * locked so a teacher can't change the question set, duration, or pass mark
     * out from under a quiz students are (or were) already being assessed against.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $teacher = $request->user()->teacherProfile;
        $this->authorizeOwner($teacher, $quiz);
        $this->assertEditable($quiz);

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
                'total_score' => $validated['total_score'] ?? null,
                'start_at' => $validated['start_at'] ?? null,
                'end_at' => $validated['end_at'] ?? null,
            ]);

            $this->syncQuestions($quiz, $validated['question_ids'] ?? [], $validated['total_score'] ?? null);
        });

        $quiz->load('subject', 'classroom')->loadCount('submissions')->load('questions');

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
        $this->assertDraft($quiz, 'published');

        if ($quiz->total_questions < 1) {
            throw ValidationException::withMessages([
                'questions' => ['Add at least one question before publishing.'],
            ]);
        }

        $quiz->update(['status' => 'Published']);
        $quiz->load('subject', 'classroom')->loadCount('submissions')->load('questions');

        AppNotification::notifyQuizPublished($quiz);

        return response()->json([
            'message' => 'Quiz published.',
            'quiz' => $this->transform($quiz),
        ]);
    }

    /**
     * Close a quiz (manually end it, or after its window has passed).
     * Only a Published quiz can be closed — a Draft has never been open to
     * students, and a Closed quiz can't be reopened this way (that would
     * silently resurrect a quiz's availability after grading may already
     * be underway).
     */
    public function close(Request $request, Quiz $quiz)
    {
        $teacher = $request->user()->teacherProfile;
        $this->authorizeOwner($teacher, $quiz);

        if ($quiz->status !== 'Published') {
            throw ValidationException::withMessages([
                'status' => ['Only a published quiz can be closed.'],
            ]);
        }

        $quiz->update(['status' => 'Closed']);
        $quiz->load('subject', 'classroom')->loadCount('submissions')->load('questions');

        return response()->json([
            'message' => 'Quiz closed.',
            'quiz' => $this->transform($quiz),
        ]);
    }

    /**
     * Delete a quiz. Only a Draft quiz can be deleted — once Published (or
     * Closed), it's kept around as a record rather than silently removable.
     */
    public function destroy(Request $request, Quiz $quiz)
    {
        $teacher = $request->user()->teacherProfile;
        $this->authorizeOwner($teacher, $quiz);
        $this->assertDraft($quiz, 'deleted');

        $quiz->delete();

        return response()->json(['message' => 'Quiz deleted successfully.']);
    }

    private function assertDraft(Quiz $quiz, string $action): void
    {
        if ($quiz->status !== 'Draft') {
            throw ValidationException::withMessages([
                'status' => ["Only a draft quiz can be {$action}. This quiz is {$quiz->status}."],
            ]);
        }
    }

    /**
     * Draft is always editable. Published is editable only while it has a
     * future start_at — i.e. it hasn't opened to students yet. A Published
     * quiz with no start_at (or a past one) is open the instant it's
     * published, so it may already have attempts in progress and is locked.
     */
    private function assertEditable(Quiz $quiz): void
    {
        if ($quiz->status === 'Draft') {
            return;
        }

        if ($quiz->status === 'Published' && $quiz->start_at && $quiz->start_at->isFuture()) {
            return;
        }

        $reason = $quiz->status === 'Published'
            ? 'it has already started'
            : 'it is Closed';

        throw ValidationException::withMessages([
            'status' => ["This quiz can no longer be edited because {$reason}."],
        ]);
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
            'total_score' => ['nullable', 'integer', 'min:1', 'max:1000'],
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

            $uniqueCount = count(array_unique($validated['question_ids']));

            if (! empty($validated['total_score']) && $validated['total_score'] < $uniqueCount) {
                throw ValidationException::withMessages([
                    'total_score' => ["Total Score must be at least {$uniqueCount} (1 point per selected question) to scale down to."],
                ]);
            }
        }

        return $validated;
    }

    /**
     * Attach the quiz's questions and, when a Total Score target is set,
     * scale each question's points to sum to it (stored per-quiz on the
     * pivot, leaving the question bank's own points untouched). Uses the
     * largest-remainder method so the scaled points always sum to exactly
     * the target despite integer rounding.
     */
    private function syncQuestions(Quiz $quiz, array $questionIds, ?int $totalScore): void
    {
        $questionIds = array_values(array_unique($questionIds));

        $pivotData = collect($questionIds)->mapWithKeys(fn ($id, $position) => [$id => ['position' => $position]])->all();

        if ($totalScore && count($questionIds) > 0) {
            $basePoints = Question::whereIn('id', $questionIds)->pluck('points', 'id');
            $scaled = $this->scalePointsToTotal($basePoints, $totalScore);

            foreach ($scaled as $id => $points) {
                $pivotData[$id]['points'] = $points;
            }
        }

        $quiz->questions()->sync($pivotData);

        $quiz->update(['total_questions' => count($questionIds)]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>  $basePoints  question_id => bank points
     * @return array<int, int> question_id => scaled points, summing to exactly $totalScore
     */
    private function scalePointsToTotal($basePoints, int $totalScore): array
    {
        $weightSum = $basePoints->sum();

        if ($weightSum <= 0) {
            $weightSum = $basePoints->count();
            $basePoints = $basePoints->map(fn () => 1);
        }

        $raw = $basePoints->map(fn ($points) => $points / $weightSum * $totalScore);
        $floored = $raw->map(fn ($value) => (int) floor($value));
        $remainder = $totalScore - $floored->sum();

        $fractionalOrder = $raw->map(fn ($value, $id) => $value - floor($value))
            ->sortDesc()
            ->keys();

        $result = $floored->all();

        foreach ($fractionalOrder->take($remainder) as $id) {
            $result[$id]++;
        }

        return $result;
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
                'points' => $quiz->pointsFor($question),
                'bankPoints' => $question->points,
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
            ])
            ->values()
            ->all();
    }

    private function transform(Quiz $quiz): array
    {
        $totalStudents = DB::table('class_student')
            ->where('class_id', $quiz->class_id)
            ->where('status', 'Active')
            ->count();

        return [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'subject_id' => $quiz->subject_id,
            'subjectCode' => $quiz->subject?->code,
            'subjectName' => $quiz->subject?->name,
            'class_id' => $quiz->class_id,
            'className' => $quiz->classroom?->name,
            'duration' => $quiz->duration_minutes,
            'maxAttempts' => $quiz->max_attempts,
            'shuffleQuestions' => $quiz->shuffle_questions,
            'shuffleOptions' => $quiz->shuffle_options,
            'passMark' => $quiz->pass_mark,
            'totalScore' => $quiz->total_score,
            'startAt' => $quiz->start_at?->format('Y-m-d\TH:i'),
            'endAt' => $quiz->end_at?->format('Y-m-d\TH:i'),
            'totalQuestions' => $quiz->total_questions,
            'totalPoints' => $quiz->relationLoaded('questions') ? $quiz->computeTotalPoints() : (int) $quiz->questions()->sum('points'),
            'submittedCount' => $quiz->submissions_count ?? 0,
            'totalStudents' => $totalStudents,
            'status' => $quiz->status,
        ];
    }
}
