<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Quiz;
use Illuminate\Support\Carbon;

class QuizTimingNotifier
{
    /**
     * Notify enrolled students ~1 hour before a published quiz closes, ~1 minute
     * before it starts, and ~1 minute before it closes. Guarded by *_reminder_sent_at
     * columns so it's idempotent no matter how often it runs.
     *
     * Called both from the scheduled `notifications:generate-quiz-timing` command
     * (the primary trigger) and from the student notifications endpoint on every
     * poll, so alerts still fire in environments where the Laravel scheduler
     * isn't wired up to a system cron (e.g. local dev).
     */
    public static function sweep(): array
    {
        $now = Carbon::now();
        $oneMinuteOut = $now->copy()->addMinute();
        $oneHourOut = $now->copy()->addHour();

        $startingSoon = Quiz::where('status', 'Published')
            ->whereNull('start_reminder_sent_at')
            ->whereNotNull('start_at')
            ->whereBetween('start_at', [$now, $oneMinuteOut])
            ->with('subject')
            ->get();

        foreach ($startingSoon as $quiz) {
            AppNotification::notifyQuizStartingSoon($quiz);
            $quiz->update(['start_reminder_sent_at' => $now]);
        }

        $closingInOneHour = Quiz::where('status', 'Published')
            ->whereNull('end_hour_reminder_sent_at')
            ->whereNotNull('end_at')
            ->whereBetween('end_at', [$now, $oneHourOut])
            ->with('subject')
            ->get();

        foreach ($closingInOneHour as $quiz) {
            AppNotification::notifyQuizClosingSoon($quiz);
            $quiz->update(['end_hour_reminder_sent_at' => $now]);
        }

        $endingSoon = Quiz::where('status', 'Published')
            ->whereNull('end_reminder_sent_at')
            ->whereNotNull('end_at')
            ->whereBetween('end_at', [$now, $oneMinuteOut])
            ->with('subject')
            ->get();

        foreach ($endingSoon as $quiz) {
            AppNotification::notifyQuizEndingSoon($quiz);
            $quiz->update(['end_reminder_sent_at' => $now]);
        }

        $closed = Quiz::autoCloseExpired();

        return [
            'startingSoon' => $startingSoon->count(),
            'closingInOneHour' => $closingInOneHour->count(),
            'endingSoon' => $endingSoon->count(),
            'closed' => $closed,
        ];
    }
}
