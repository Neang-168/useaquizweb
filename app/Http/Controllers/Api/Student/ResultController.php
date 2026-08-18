<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\QuizSubmission;
use Illuminate\Http\Request;

class ResultController extends Controller
{
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
            ->with(['quiz.subject', 'quiz.classroom', 'quiz.questions'])
            ->orderByDesc('submitted_at')
            ->get();

        return response()->json([
            'data' => $submissions->map(fn (QuizSubmission $submission) => $this->transform($submission))->values(),
        ]);
    }

    private function transform(QuizSubmission $submission): array
    {
        $quiz = $submission->quiz;
        $totalPoints = (int) ($quiz?->questions->sum('points') ?? 0);
        $score = $submission->mcq_score + ($submission->essay_score ?? 0);
        $passMark = $quiz?->pass_mark ?? 50;
        $percentage = $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;

        return [
            'id' => $submission->id,
            'quizId' => $quiz?->id,
            'quizTitle' => $quiz?->title,
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
        ];
    }
}
