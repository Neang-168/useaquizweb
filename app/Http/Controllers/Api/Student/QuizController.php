<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionMatchingPair;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\StudentEnrollment;
use App\Models\SubmissionAnswer;
use App\Models\SubmissionAnswerMatch;
use App\Models\SubmissionAnswerOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class QuizController extends Controller
{
    /**
     * List quizzes available to the logged-in student: published, in the
     * student's enrolled class(es), within their start/end window (if set),
     * plus the student's own attempt history for each so the UI can show
     * not started / submitted / graded.
     */
    public function index(Request $request)
    {
        $student = $request->user()->studentProfile;

        if (! $student) {
            return response()->json(['data' => []]);
        }

        $classIds = $this->enrolledClassIds($student);

        // Every Published quiz in the student's classes is listed here
        // (open, upcoming, or past its window) so the UI can group them
        // into active/upcoming tabs; window + status access control is
        // enforced separately in show()/submit().
        $quizzes = Quiz::query()
            ->where('status', 'Published')
            ->whereIn('class_id', $classIds)
            ->with(['subject', 'classroom'])
            ->withSum('questions as total_points', 'points')
            ->orderByDesc('created_at')
            ->get();

        $submissions = QuizSubmission::whereIn('quiz_id', $quizzes->pluck('id'))
            ->where('student_profile_id', $student->id)
            ->get()
            ->groupBy('quiz_id');

        return response()->json([
            'data' => $quizzes->map(fn (Quiz $quiz) => $this->transform($quiz, $submissions->get($quiz->id, collect())))->values(),
        ]);
    }

    /**
     * Fetch one quiz for taking: questions (and their options/pairs)
     * shuffled per the quiz's settings, with all correctness data stripped.
     * Blocked if the quiz isn't open to this student right now, or they've
     * already used up their attempts.
     */
    public function show(Request $request, Quiz $quiz)
    {
        $student = $request->user()->studentProfile;
        $this->authorizeAccess($student, $quiz);

        $attemptsUsed = QuizSubmission::where('quiz_id', $quiz->id)
            ->where('student_profile_id', $student->id)
            ->count();

        if ($attemptsUsed >= $quiz->max_attempts) {
            throw ValidationException::withMessages([
                'quiz' => ['You have used all of your attempts for this quiz.'],
            ]);
        }

        $quiz->load('subject', 'classroom')->loadSum('questions as total_points', 'points');

        return response()->json([
            'quiz' => array_merge($this->transform($quiz, collect()), [
                'attemptNumber' => $attemptsUsed + 1,
                'questions' => $this->transformQuestionsForTaking($quiz),
            ]),
        ]);
    }

    /**
     * Submit answers for a quiz attempt. Creates the submission and its
     * answers inside a transaction; the model observers grade it as the
     * rows land, so the score is already correct by the time this returns.
     */
    public function submit(Request $request, Quiz $quiz)
    {
        $student = $request->user()->studentProfile;
        $this->authorizeAccess($student, $quiz);

        $attemptsUsed = QuizSubmission::where('quiz_id', $quiz->id)
            ->where('student_profile_id', $student->id)
            ->count();

        if ($attemptsUsed >= $quiz->max_attempts) {
            throw ValidationException::withMessages([
                'quiz' => ['You have used all of your attempts for this quiz.'],
            ]);
        }

        $validated = $request->validate([
            'answers' => ['array'],
            'answers.*.questionId' => ['required', 'integer'],
            'answers.*.selectedOptionId' => ['nullable', 'integer'],
            'answers.*.selectedOptionIds' => ['array'],
            'answers.*.selectedOptionIds.*' => ['integer'],
            'answers.*.matches' => ['array'],
            'answers.*.matches.*.leftPairId' => ['required', 'integer'],
            'answers.*.matches.*.selectedRightPairId' => ['nullable', 'integer'],
        ]);

        $questions = $quiz->questions()->with('options', 'matchingPairs')->get()->keyBy('id');

        $submission = DB::transaction(function () use ($validated, $quiz, $student, $attemptsUsed, $questions) {
            $submission = QuizSubmission::create([
                'quiz_id' => $quiz->id,
                'student_profile_id' => $student->id,
                'attempt_number' => $attemptsUsed + 1,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            foreach ($validated['answers'] ?? [] as $answerInput) {
                $question = $questions->get($answerInput['questionId']);

                if (! $question) {
                    continue;
                }

                $answer = SubmissionAnswer::create([
                    'quiz_submission_id' => $submission->id,
                    'question_id' => $question->id,
                    'selected_option_id' => $question->type === 'true_false'
                        ? $this->validOptionId($question, $answerInput['selectedOptionId'] ?? null)
                        : null,
                ]);

                if ($question->type === 'multiple_choice') {
                    $validOptionIds = $question->options->pluck('id');

                    foreach (array_unique($answerInput['selectedOptionIds'] ?? []) as $optionId) {
                        if ($validOptionIds->contains($optionId)) {
                            SubmissionAnswerOption::create([
                                'submission_answer_id' => $answer->id,
                                'question_option_id' => $optionId,
                            ]);
                        }
                    }
                }

                if ($question->type === 'matching') {
                    $validPairIds = $question->matchingPairs->pluck('id');

                    foreach ($answerInput['matches'] ?? [] as $match) {
                        if (! $validPairIds->contains($match['leftPairId'])) {
                            continue;
                        }

                        $selectedRight = $match['selectedRightPairId'] ?? null;

                        SubmissionAnswerMatch::create([
                            'submission_answer_id' => $answer->id,
                            'left_pair_id' => $match['leftPairId'],
                            'selected_right_pair_id' => $validPairIds->contains($selectedRight) ? $selectedRight : null,
                        ]);
                    }
                }
            }

            return $submission;
        });

        $submission->refresh();
        $totalPoints = (int) $quiz->questions()->sum('points');
        $passMark = $quiz->pass_mark ?? 50;
        $score = $submission->mcq_score + ($submission->essay_score ?? 0);
        $percentage = $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;

        return response()->json([
            'message' => 'Quiz submitted successfully.',
            'result' => [
                'submissionId' => $submission->id,
                'attemptNumber' => $submission->attempt_number,
                'score' => $score,
                'totalPoints' => $totalPoints,
                'percentage' => round($percentage, 1),
                'passMark' => $passMark,
                'passed' => $percentage >= $passMark,
            ],
        ]);
    }

    private function validOptionId(Question $question, ?int $optionId): ?int
    {
        return $optionId && $question->options->pluck('id')->contains($optionId) ? $optionId : null;
    }

    private function authorizeAccess(?object $student, Quiz $quiz): void
    {
        if (! $student) {
            abort(403);
        }

        $classIds = $this->enrolledClassIds($student);
        $now = now();

        if (
            $quiz->status !== 'Published'
            || ! $classIds->contains($quiz->class_id)
            || ($quiz->start_at && $quiz->start_at->gt($now))
            || ($quiz->end_at && $quiz->end_at->lt($now))
        ) {
            abort(403, 'This quiz is not currently available to you.');
        }
    }

    private function enrolledClassIds(object $student)
    {
        return StudentEnrollment::where('student_profile_id', $student->id)
            ->where('status', 'Active')
            ->pluck('class_id')
            ->unique();
    }

    private function transformQuestionsForTaking(Quiz $quiz): array
    {
        $questions = $quiz->orderedQuestions()->with('options', 'matchingPairs')->get();

        if ($quiz->shuffle_questions) {
            $questions = $questions->shuffle();
        }

        return $questions->map(function (Question $question) use ($quiz) {
            $options = $question->options->sortBy('position')->values();
            $pairs = $question->matchingPairs->sortBy('position')->values();

            if ($quiz->shuffle_options) {
                $options = $options->shuffle()->values();
            }

            $rightPairs = $quiz->shuffle_options ? $pairs->shuffle()->values() : $pairs;

            return [
                'id' => $question->id,
                'type' => $question->type,
                'points' => $question->points,
                'title' => $question->title,
                'imageUrl' => $question->image_path ? Storage::disk('public')->url($question->image_path) : null,
                'imageAlt' => $question->image_alt,
                'options' => $options->map(fn (QuestionOption $option) => [
                    'id' => $option->id,
                    'text' => $option->text,
                    'imageUrl' => $option->image_path ? Storage::disk('public')->url($option->image_path) : null,
                ])->values(),
                'leftItems' => $pairs->map(fn (QuestionMatchingPair $pair) => [
                    'pairId' => $pair->id,
                    'text' => $pair->left_text,
                    'imageUrl' => $pair->left_image_path ? Storage::disk('public')->url($pair->left_image_path) : null,
                ])->values(),
                'rightItems' => $rightPairs->map(fn (QuestionMatchingPair $pair) => [
                    'pairId' => $pair->id,
                    'text' => $pair->right_text,
                    'imageUrl' => $pair->right_image_path ? Storage::disk('public')->url($pair->right_image_path) : null,
                ])->values(),
            ];
        })->values()->all();
    }

    private function transform(Quiz $quiz, $submissions): array
    {
        $latest = $submissions->sortByDesc('attempt_number')->first();
        $best = $submissions->sortByDesc(fn (QuizSubmission $s) => $s->mcq_score + ($s->essay_score ?? 0))->first();
        $totalPoints = (int) ($quiz->total_points ?? 0);
        $attemptsUsed = $submissions->count();
        $now = now();
        $hasStarted = ! $quiz->start_at || $quiz->start_at->lte($now);
        $hasEnded = $quiz->end_at && $quiz->end_at->lt($now);

        return [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'subject' => $quiz->subject?->name,
            'subjectCode' => $quiz->subject?->code,
            'className' => $quiz->classroom?->name,
            'duration' => $quiz->duration_minutes,
            'maxAttempts' => $quiz->max_attempts,
            'attemptsUsed' => $attemptsUsed,
            'passMark' => $quiz->pass_mark ?? 50,
            'startAt' => $quiz->start_at?->format('Y-m-d\TH:i'),
            'endAt' => $quiz->end_at?->format('Y-m-d\TH:i'),
            'totalQuestions' => $quiz->total_questions,
            'totalPoints' => $totalPoints,
            'status' => $latest ? $latest->status : 'not_started',
            'isUpcoming' => ! $hasStarted,
            'isOpen' => $hasStarted && ! $hasEnded && $attemptsUsed < $quiz->max_attempts,
            'bestScore' => $best ? $best->mcq_score + ($best->essay_score ?? 0) : null,
            'bestPercentage' => $best && $totalPoints > 0
                ? round((($best->mcq_score + ($best->essay_score ?? 0)) / $totalPoints) * 100, 1)
                : null,
            'lastSubmittedAt' => $latest?->submitted_at?->format('Y-m-d\TH:i:s'),
        ];
    }
}
