<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizSubmission;

class ReportController extends Controller
{
    /**
     * School-wide quiz report: every completed submission with student,
     * quiz, and enrollment context. The Admin report page filters,
     * searches, and summarizes this set client-side.
     */
    public function index()
    {
        $submissions = QuizSubmission::query()
            ->whereIn('status', ['submitted', 'graded'])
            ->with([
                'quiz.questions',
                'studentProfile.user',
                'studentProfile.enrollments' => fn ($query) => $query->latest('enrollment_date')->with(
                    'faculty', 'department', 'major', 'stage', 'shift', 'term', 'promotion', 'academicYear', 'semester'
                ),
            ])
            ->orderByDesc('submitted_at')
            ->get()
            ->map(fn (QuizSubmission $submission) => $this->transform($submission))
            ->values();

        return response()->json(['data' => $submissions]);
    }

    private function transform(QuizSubmission $submission): array
    {
        $quiz = $submission->quiz;
        $hasEssay = $quiz?->questions->contains(fn ($question) => $question->type === 'essay') ?? false;
        $totalPoints = $submission->total_points ?? ($quiz?->computeTotalPoints() ?? 0);
        $passMark = $submission->pass_mark ?? ($quiz?->pass_mark ?? 50);
        $score = $submission->mcq_score + ($submission->essay_score ?? 0);
        $percentage = $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;

        $status = $hasEssay && is_null($submission->essay_score)
            ? 'Pending'
            : ($percentage >= $passMark ? 'Passed' : 'Failed');

        $timeSpentSeconds = $submission->started_at && $submission->submitted_at
            ? $submission->started_at->diffInSeconds($submission->submitted_at)
            : null;

        $enrollment = $submission->studentProfile?->enrollments->first();
        $user = $submission->studentProfile?->user;

        return [
            'id' => $submission->id,
            'code' => $submission->studentProfile?->student_code,
            'name' => trim(($user?->first_name ?? '').' '.($user?->last_name ?? '')),
            'username' => $user?->username,
            'gender' => $user?->gender,
            'phone' => $user?->phone,
            'quizTitle' => $quiz?->title,
            'attempts' => $submission->attempt_number,
            'score' => round($percentage),
            'timeSpent' => $timeSpentSeconds !== null ? $this->formatDuration($timeSpentSeconds) : '—',
            'timeSpentSeconds' => $timeSpentSeconds,
            'submittedAt' => $submission->submitted_at?->format('Y-m-d'),
            'status' => $status,
            'facultyId' => $enrollment?->faculty_id,
            'facultyName' => $enrollment?->faculty?->name,
            'departmentId' => $enrollment?->department_id,
            'departmentName' => $enrollment?->department?->name,
            'majorId' => $enrollment?->major_id,
            'majorName' => $enrollment?->major?->name,
            'stageId' => $enrollment?->stage_id,
            'stageName' => $enrollment?->stage?->name,
            'shiftId' => $enrollment?->shift_id,
            'shiftName' => $enrollment?->shift?->name,
            'termId' => $enrollment?->term_id,
            'termName' => $enrollment?->term?->name,
            'promotionId' => $enrollment?->promotion_id,
            'promotionName' => $enrollment?->promotion?->name
                ?: ($enrollment?->promotion ? "{$enrollment->promotion->year_start}-{$enrollment->promotion->year_end}" : null),
            'academicYearId' => $enrollment?->academic_year_id,
            'academicYearName' => $enrollment?->academicYear?->name,
            'semesterId' => $enrollment?->semester_id,
            'semesterName' => $enrollment?->semester?->name,
            'enrollmentDate' => $enrollment?->enrollment_date?->toDateString(),
            'classId' => $quiz?->class_id,
            'subjectId' => $quiz?->subject_id,
            'teacherId' => $quiz?->teacher_profile_id,
        ];
    }

    private function formatDuration(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remaining = $seconds % 60;

        return "{$minutes}m {$remaining}s";
    }
}
