<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\StudentEnrollment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Get this student's quizzes formatted for calendar rendering, scoped to one month.
     */
    public function index(Request $request)
    {
        $student = $request->user()->studentProfile;

        if (! $student) {
            return response()->json(['data' => []]);
        }

        Quiz::autoCloseExpired();

        $classIds = StudentEnrollment::where('student_profile_id', $student->id)
            ->where('status', 'Active')
            ->pluck('class_id')
            ->unique();

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();

        $quizzes = Quiz::query()
            ->where('status', 'Published')
            ->whereIn('class_id', $classIds)
            ->where(function ($query) {
                $query->whereNotNull('start_at')->orWhereNotNull('end_at');
            })
            ->with(['subject', 'classroom', 'questions'])
            ->get()
            ->filter(function (Quiz $quiz) use ($monthStart, $monthEnd) {
                $displayStart = $quiz->start_at ?? $quiz->end_at;
                $displayEnd = $quiz->end_at ?? $quiz->start_at;

                return $displayStart->lte($monthEnd) && $displayEnd->gte($monthStart);
            });

        $quizzes->each(fn (Quiz $quiz) => $quiz->total_points = $quiz->computeTotalPoints());

        return response()->json([
            'data' => $quizzes->map(fn (Quiz $quiz) => $this->transform($quiz))->values(),
        ]);
    }

    private function transform(Quiz $quiz): array
    {
        $displayStart = $quiz->start_at ?? $quiz->end_at;
        $displayEnd = $quiz->end_at ?? $quiz->start_at;

        return [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'status' => $quiz->status,
            'subject' => $quiz->subject ? [
                'id' => $quiz->subject->id,
                'name' => $quiz->subject->name,
                'code' => $quiz->subject->code,
            ] : null,
            'class' => $quiz->classroom ? [
                'id' => $quiz->classroom->id,
                'name' => $quiz->classroom->name,
            ] : null,
            'startDate' => $displayStart->format('Y-m-d'),
            'endDate' => $displayEnd->format('Y-m-d'),
            'startAt' => $quiz->start_at?->format('Y-m-d\TH:i'),
            'endAt' => $quiz->end_at?->format('Y-m-d\TH:i'),
            'totalQuestions' => $quiz->total_questions,
            'totalPoints' => (int) ($quiz->total_points ?? 0),
            'duration' => $quiz->duration_minutes,
            'maxAttempts' => $quiz->max_attempts,
            'passMark' => $quiz->pass_mark,
        ];
    }
}
