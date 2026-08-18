<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Get the students of a quiz along with their score and feedback status.
     */
    public function index(Request $request, Quiz $quiz)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher || $quiz->teacher_profile_id !== $teacher->id) {
            abort(403);
        }

        $submissions = $quiz->submissions()
            ->with(['studentProfile.user', 'feedback'])
            ->orderBy('submitted_at')
            ->get();

        $passMark = $quiz->pass_mark ?? 50;
        $totalPoints = (int) $quiz->questions()->sum('points');

        $data = $submissions->map(function (QuizSubmission $submission) use ($passMark, $totalPoints) {
            $score = $submission->mcq_score + ($submission->essay_score ?? 0);
            $percentage = $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;

            return [
                'submissionId' => $submission->id,
                'studentId' => $submission->studentProfile?->student_code,
                'name' => trim($submission->studentProfile?->user?->first_name . ' ' . $submission->studentProfile?->user?->last_name),
                'score' => $score,
                'totalPoints' => $totalPoints,
                'passMark' => $passMark,
                'passed' => $percentage >= $passMark,
                'hasFeedbackSent' => $submission->feedback->isNotEmpty(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Send feedback to a student for a specific submission.
     */
    public function store(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        $validated = $request->validate([
            'quiz_submission_id' => ['required', 'exists:quiz_submissions,id'],
            'message' => ['required', 'string'],
        ]);

        $submission = QuizSubmission::with('quiz')->findOrFail($validated['quiz_submission_id']);

        if (! $teacher || $submission->quiz?->teacher_profile_id !== $teacher->id) {
            abort(403);
        }

        $feedback = Feedback::create([
            'teacher_profile_id' => $teacher->id,
            'student_profile_id' => $submission->student_profile_id,
            'quiz_submission_id' => $submission->id,
            'message' => $validated['message'],
            'sent_at' => now(),
        ]);

        return response()->json([
            'message' => 'Feedback sent successfully.',
            'feedback' => [
                'id' => $feedback->id,
                'sentAt' => $feedback->sent_at->format('Y-m-d H:i'),
            ],
        ], 201);
    }
}
