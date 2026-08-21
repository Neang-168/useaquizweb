<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\StudentEnrollment;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Aggregate stats for the teacher dashboard.
     */
    public function index(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            return response()->json([
                'classesCount' => 0,
                'studentsCount' => 0,
                'activeQuizzesCount' => 0,
                'pendingEssaysCount' => 0,
                'averageScore' => 0,
                'recentQuizzes' => [],
                'needsAttention' => [],
                'upcomingQuizzes' => [],
            ]);
        }

        Quiz::autoCloseExpired();

        $classIds = TeacherSubject::where('teacher_profile_id', $teacher->id)
            ->whereNotNull('class_id')
            ->pluck('class_id')
            ->unique();

        $studentsCount = StudentEnrollment::whereIn('class_id', $classIds)
            ->where('status', 'Active')
            ->count();

        $quizzes = Quiz::where('teacher_profile_id', $teacher->id)
            ->with('questions')
            ->get();
        $quizzes->each(fn (Quiz $quiz) => $quiz->total_points = $quiz->computeTotalPoints());
        $activeQuizzesCount = $quizzes->where('status', 'Published')->count();

        $quizIds = $quizzes->pluck('id');
        $submissions = QuizSubmission::whereIn('quiz_id', $quizIds)->get();

        $pendingEssaysCount = $submissions->where('status', 'submitted')->filter(function (QuizSubmission $submission) use ($quizzes) {
            $quiz = $quizzes->firstWhere('id', $submission->quiz_id);

            return $quiz && is_null($submission->essay_score);
        })->count();

        // pass_mark is a percentage, and quizzes can total any number of
        // points, so "score" only means something as score / totalPoints —
        // comparing raw scores across quizzes with different point totals
        // isn't meaningful.
        $percentageOf = function (QuizSubmission $s) use ($quizzes): float {
            $quiz = $quizzes->firstWhere('id', $s->quiz_id);
            $totalPoints = $s->total_points ?? (int) ($quiz?->total_points ?? 0);
            $score = $s->mcq_score + ($s->essay_score ?? 0);

            return $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0.0;
        };

        $averageScore = $submissions->isNotEmpty()
            ? round($submissions->avg($percentageOf), 1)
            : 0;

        $recentQuizzes = $quizzes->sortByDesc('created_at')->take(5)->values()->map(function (Quiz $quiz) use ($submissions, $classIds) {
            $quiz->load('subject', 'classroom');
            $totalStudents = StudentEnrollment::where('class_id', $quiz->class_id)->where('status', 'Active')->count();

            return [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'questionsCount' => $quiz->total_questions,
                'duration' => $quiz->duration_minutes,
                'subject' => $quiz->subject?->name,
                'className' => $quiz->classroom?->name,
                'submittedCount' => $submissions->where('quiz_id', $quiz->id)->count(),
                'totalStudents' => $totalStudents,
                'statusText' => $quiz->status,
            ];
        });

        $needsAttention = $submissions
            ->filter(function (QuizSubmission $s) use ($quizzes, $percentageOf) {
                $passMark = $quizzes->firstWhere('id', $s->quiz_id)?->pass_mark ?? 50;

                return $percentageOf($s) < $passMark;
            })
            ->sortBy($percentageOf)
            ->take(4)
            ->values()
            ->map(function (QuizSubmission $submission) use ($quizzes) {
                $quiz = $quizzes->firstWhere('id', $submission->quiz_id);
                $submission->load('studentProfile.user');

                return [
                    'name' => trim($submission->studentProfile?->user?->first_name . ' ' . $submission->studentProfile?->user?->last_name),
                    'className' => $quiz?->classroom?->name,
                    'subject' => $quiz?->subject?->name,
                    'score' => $submission->mcq_score + ($submission->essay_score ?? 0),
                ];
            });

        $now = now();
        $upcomingQuizzes = $quizzes
            ->filter(function (Quiz $quiz) use ($now) {
                $displayEnd = $quiz->end_at ?? $quiz->start_at;

                return $displayEnd && $displayEnd->gte($now);
            })
            ->sortBy(fn (Quiz $quiz) => $quiz->start_at ?? $quiz->end_at)
            ->take(5)
            ->values()
            ->map(function (Quiz $quiz) {
                $quiz->load('subject', 'classroom');

                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'subject' => $quiz->subject?->name,
                    'className' => $quiz->classroom?->name,
                    'startAt' => $quiz->start_at?->format('Y-m-d\TH:i'),
                    'endAt' => $quiz->end_at?->format('Y-m-d\TH:i'),
                    'status' => $quiz->status,
                ];
            });

        return response()->json([
            'classesCount' => $classIds->count(),
            'studentsCount' => $studentsCount,
            'activeQuizzesCount' => $activeQuizzesCount,
            'pendingEssaysCount' => $pendingEssaysCount,
            'averageScore' => $averageScore,
            'recentQuizzes' => $recentQuizzes,
            'needsAttention' => $needsAttention,
            'upcomingQuizzes' => $upcomingQuizzes,
        ]);
    }
}
