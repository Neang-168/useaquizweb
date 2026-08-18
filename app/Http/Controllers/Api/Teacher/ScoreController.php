<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    /**
     * Get the score sheet for one of this teacher's quizzes.
     */
    public function index(Request $request, Quiz $quiz)
    {
        $this->authorizeOwner($request, $quiz);

        $hasEssay = $quiz->questions()->where('type', 'essay')->exists();
        $passMark = $quiz->pass_mark ?? 50;
        $totalPoints = (int) $quiz->questions()->sum('points');

        $submissions = $quiz->submissions()
            ->with('studentProfile.user')
            ->orderBy('submitted_at')
            ->get();

        $data = $submissions->map(function (QuizSubmission $submission) use ($hasEssay, $passMark, $totalPoints) {
            $totalScore = $submission->mcq_score + ($submission->essay_score ?? 0);
            $percentage = $totalPoints > 0 ? ($totalScore / $totalPoints) * 100 : 0;

            return [
                'id' => $submission->id,
                'studentId' => $submission->studentProfile?->student_code,
                'name' => trim($submission->studentProfile?->user?->first_name . ' ' . $submission->studentProfile?->user?->last_name),
                'submittedAt' => $submission->submitted_at?->format('Y-m-d h:i A'),
                'mcqScore' => $submission->mcq_score,
                'essayScore' => $submission->essay_score,
                'essayNeedsGrade' => $hasEssay && is_null($submission->essay_score),
                'passMark' => $passMark,
                'passed' => $percentage >= $passMark,
            ];
        });

        return response()->json(['data' => $data, 'totalPoints' => $totalPoints]);
    }

    /**
     * Grade (or re-grade) the essay portion of a submission.
     */
    public function gradeEssay(Request $request, QuizSubmission $submission)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher || $submission->quiz?->teacher_profile_id !== $teacher->id) {
            abort(403);
        }

        $validated = $request->validate([
            'essay_score' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $submission->update([
            'essay_score' => $validated['essay_score'],
            'status' => 'graded',
        ]);

        return response()->json([
            'message' => 'Essay score saved successfully.',
            'submission' => [
                'id' => $submission->id,
                'essayScore' => $submission->essay_score,
                'essayNeedsGrade' => false,
            ],
        ]);
    }

    private function authorizeOwner(Request $request, Quiz $quiz): void
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher || $quiz->teacher_profile_id !== $teacher->id) {
            abort(403);
        }
    }
}
