<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Aggregate stats for the student dashboard.
     */
    public function index(Request $request)
    {
        $student = $request->user()->studentProfile;

        if (! $student) {
            return response()->json([
                'todoCount' => 0,
                'completedCount' => 0,
                'averageScore' => 0,
                'enrolledSubjectsCount' => 0,
                'dueSoon' => [],
                'recentQuizzes' => [],
                'announcements' => [],
            ]);
        }

        Quiz::autoCloseExpired();

        $classIds = StudentEnrollment::where('student_profile_id', $student->id)
            ->where('status', 'Active')
            ->pluck('class_id')
            ->unique();

        $enrolledSubjectsCount = StudentEnrollment::where('student_profile_id', $student->id)
            ->where('status', 'Active')
            ->count();

        $now = now();

        $publishedQuizzes = Quiz::where('status', 'Published')
            ->whereIn('class_id', $classIds)
            ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', $now))
            ->with('subject', 'classroom', 'questions')
            ->get();
        $publishedQuizzes->each(fn (Quiz $quiz) => $quiz->total_points = $quiz->computeTotalPoints());

        // Every Published quiz for this student (open or not yet started), so a
        // teacher's publish action shows up on the dashboard right away even if
        // the quiz's window hasn't opened yet or it isn't due "soon".
        $recentlyPublishedQuizzes = Quiz::where('status', 'Published')
            ->whereIn('class_id', $classIds)
            ->with('subject', 'classroom', 'questions')
            ->orderByDesc('created_at')
            ->take(3)
            ->get();
        $recentlyPublishedQuizzes->each(fn (Quiz $quiz) => $quiz->total_points = $quiz->computeTotalPoints());

        $submissions = QuizSubmission::where('student_profile_id', $student->id)->get();
        $attemptsByQuiz = $submissions->groupBy('quiz_id');

        $todoQuizzes = $publishedQuizzes->filter(
            fn (Quiz $quiz) => $attemptsByQuiz->get($quiz->id, collect())->count() < $quiz->max_attempts
        );

        $percentageOf = function (QuizSubmission $s) use ($publishedQuizzes): float {
            $totalPoints = $s->total_points ?? (int) ($publishedQuizzes->firstWhere('id', $s->quiz_id)?->total_points ?? 0);
            $score = $s->mcq_score + ($s->essay_score ?? 0);

            return $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0.0;
        };

        $gradedSubmissions = $submissions->whereIn('status', ['submitted', 'graded']);
        $averageScore = $gradedSubmissions->isNotEmpty()
            ? round($gradedSubmissions->avg($percentageOf), 1)
            : 0;

        $dueSoon = $todoQuizzes
            ->sortBy(fn (Quiz $quiz) => $quiz->end_at ?? $quiz->start_at ?? $now)
            ->take(5)
            ->values()
            ->map(fn (Quiz $quiz) => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'subject' => $quiz->subject?->name,
                'subjectId' => $quiz->subject_id,
                'classId' => $quiz->class_id,
                'className' => $quiz->classroom?->name,
                'duration' => $quiz->duration_minutes,
                'totalQuestions' => $quiz->total_questions,
                'startAt' => $quiz->start_at?->format('Y-m-d\TH:i'),
                'endAt' => $quiz->end_at?->format('Y-m-d\TH:i'),
            ]);

        $recentQuizzes = $recentlyPublishedQuizzes
            ->map(function (Quiz $quiz) use ($attemptsByQuiz, $now) {
                $attemptsUsed = $attemptsByQuiz->get($quiz->id, collect())->count();

                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'subject' => $quiz->subject?->name,
                    'subjectId' => $quiz->subject_id,
                    'classId' => $quiz->class_id,
                    'className' => $quiz->classroom?->name,
                    'duration' => $quiz->duration_minutes,
                    'totalQuestions' => $quiz->total_questions,
                    'startAt' => $quiz->start_at?->format('Y-m-d\TH:i'),
                    'endAt' => $quiz->end_at?->format('Y-m-d\TH:i'),
                    'isUpcoming' => (bool) ($quiz->start_at && $quiz->start_at->gt($now)),
                    'isClosed' => (bool) ($quiz->end_at && $quiz->end_at->lt($now)),
                    'attemptsUsed' => $attemptsUsed,
                    'maxAttempts' => $quiz->max_attempts,
                    'attemptsExhausted' => $attemptsUsed >= $quiz->max_attempts,
                    'publishedAt' => $quiz->created_at?->format('Y-m-d\TH:i:s'),
                ];
            });

        $announcements = Feedback::where('student_profile_id', $student->id)
            ->with('teacherProfile.user')
            ->orderByDesc('sent_at')
            ->take(5)
            ->get()
            ->map(fn (Feedback $feedback) => [
                'id' => $feedback->id,
                'message' => $feedback->message,
                'teacherName' => $feedback->teacherProfile?->user?->full_name,
                'sentAt' => $feedback->sent_at?->format('Y-m-d\TH:i:s'),
            ]);

        return response()->json([
            'todoCount' => $todoQuizzes->count(),
            'completedCount' => $gradedSubmissions->count(),
            'averageScore' => $averageScore,
            'enrolledSubjectsCount' => $enrolledSubjectsCount,
            'dueSoon' => $dueSoon,
            'recentQuizzes' => $recentQuizzes,
            'announcements' => $announcements,
        ]);
    }
}
