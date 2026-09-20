<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Concerns\DescribesQuestionAnswers;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResultController extends Controller
{
    use DescribesQuestionAnswers;

    /**
     * The logged-in student's own past submissions with scores and quiz
     * metadata, most recent first.
     */
    public function index(Request $request)
    {
        $student = $request->user()->studentProfile;

        if (! $student) {
            return response()->json(['data' => []]);
        }

        $submissions = QuizSubmission::where('student_profile_id', $student->id)
            ->whereIn('status', ['submitted', 'graded'])
            ->when($request->input('class_id'), fn ($q, $id) => $q->whereHas('quiz', fn ($qq) => $qq->where('class_id', $id)))
            ->when($request->input('subject_id'), fn ($q, $id) => $q->whereHas('quiz', fn ($qq) => $qq->where('subject_id', $id)))
            ->when($request->input('quiz_id'), fn ($q, $id) => $q->where('quiz_id', $id))
            ->with(['quiz.subject', 'quiz.classroom', 'quiz.questions'])
            ->orderByDesc('submitted_at')
            ->get();

        $submissions->each(function (QuizSubmission $submission) {
            if ($submission->quiz) {
                $submission->quiz->total_points = $submission->quiz->computeTotalPoints();
            }
        });

        return response()->json([
            'data' => $submissions->map(fn (QuizSubmission $submission) => $this->transform($submission))->values(),
        ]);
    }

    private function transform(QuizSubmission $submission): array
    {
        $quiz = $submission->quiz;
        $totalPoints = $submission->total_points ?? (int) ($quiz?->total_points ?? 0);
        $score = $submission->mcq_score + ($submission->essay_score ?? 0);
        $passMark = $submission->pass_mark ?? ($quiz?->pass_mark ?? 50);
        $percentage = $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;

        return [
            'id' => $submission->id,
            'quizId' => $quiz?->id,
            'quizTitle' => $quiz?->title,
            'subjectId' => $quiz?->subject_id,
            'classId' => $quiz?->class_id,
            'subject' => $quiz?->subject?->name,
            'className' => $quiz?->classroom?->name,
            'attemptNumber' => $submission->attempt_number,
            'submittedAt' => $submission->submitted_at?->format('Y-m-d\TH:i:s'),
            'status' => $submission->status,
            'score' => $score,
            'totalPoints' => $totalPoints,
            'percentage' => round($percentage, 1),
            'passMark' => $passMark,
            'passed' => $percentage >= $passMark,
            'endAt' => $quiz?->end_at?->format('Y-m-d\TH:i:s'),
            'canViewAnswer' => $this->canViewAnswer($quiz),
        ];
    }

    /**
     * The student can only see correct answers once the quiz's deadline has
     * passed — otherwise students who finish early could leak answers to
     * classmates who haven't taken it yet. No deadline configured means
     * there's nothing to wait for, so it's available right away.
     */
    private function canViewAnswer(?Quiz $quiz): bool
    {
        if (! $quiz) {
            return false;
        }

        if (! $quiz->end_at) {
            return true;
        }

        return $quiz->status === 'Closed' || now()->greaterThanOrEqualTo($quiz->end_at);
    }

    /**
     * The logged-in student's own per-question correct-answer breakdown for
     * one of their submissions — locked until the quiz's end date passes.
     */
    public function answers(Request $request, QuizSubmission $submission)
    {
        $student = $request->user()->studentProfile;

        if (! $student || $submission->student_profile_id !== $student->id) {
            abort(403);
        }

        Quiz::autoCloseExpired();

        $submission->load('quiz');
        $quiz = $submission->quiz;

        if (! $this->canViewAnswer($quiz)) {
            return response()->json([
                'available' => false,
                'availableAt' => $quiz?->end_at?->format('Y-m-d\TH:i:s'),
            ], 423);
        }

        $submission->load([
            'answers.selectedOption',
            'answers.selectedOptions.option',
            'answers.matches.leftPair',
            'answers.matches.selectedRightPair',
        ]);
        $quiz->load(['questions', 'orderedQuestions.options', 'orderedQuestions.matchingPairs']);

        $totalPoints = $submission->total_points ?? $quiz->computeTotalPoints();
        $passMark = $submission->pass_mark ?? ($quiz->pass_mark ?? 50);
        $score = $submission->mcq_score + ($submission->essay_score ?? 0);
        $percentage = $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;

        $answersByQuestion = $submission->answers->keyBy('question_id');

        $questions = $quiz->orderedQuestions->map(function (Question $question) use ($answersByQuestion, $quiz) {
            $answer = $answersByQuestion->get($question->id);

            return [
                'questionId' => $question->id,
                'title' => $question->title,
                'type' => $question->type,
                'imageUrl' => $question->image_path ? Storage::disk('public')->url($question->image_path) : null,
                'imageAlt' => $question->image_alt,
                'pointsPossible' => $quiz->pointsFor($question),
                'pointsAwarded' => $answer->awarded_score ?? 0,
                'isCorrect' => (bool) ($answer->is_correct ?? false),
                'answered' => $answer !== null,
                ...$this->describeAnswer($question, $answer),
            ];
        })->values();

        return response()->json([
            'available' => true,
            'score' => $score,
            'totalPoints' => $totalPoints,
            'scorePercentage' => round($percentage),
            'passMark' => $passMark,
            'passed' => $percentage >= $passMark,
            'questions' => $questions,
        ]);
    }
}
