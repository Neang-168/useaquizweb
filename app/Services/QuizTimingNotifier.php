<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Quiz;
use Illuminate\Support\Carbon;

class QuizTimingNotifier
{
    /**
     * Notify enrolled students ~5 minutes before a published quiz starts. Guarded
     * by the start_reminder_sent_at column so it's idempotent no matter how often
     * it runs.
     *
     * Called both from the scheduled `notifications:generate-quiz-timing` command
     * (the primary trigger) and from the student notifications endpoint on every
     * poll, so alerts still fire in environments where the Laravel scheduler
     * isn't wired up to a system cron (e.g. local dev).
     */
    public static function sweep(): array
    {
        $now = Carbon::now();
        $fiveMinutesOut = $now->copy()->addMinutes(5);

        $startingSoonIds = Quiz::where('status', 'Published')
            ->whereNull('start_reminder_sent_at')
            ->whereNotNull('start_at')
            ->whereBetween('start_at', [$now, $fiveMinutesOut])
            ->pluck('id');

        $startingSoonCount = 0;
        foreach ($startingSoonIds as $quizId) {
            // Atomically claim this quiz's reminder before notifying, so concurrent
            // sweeps (many students polling at once) can't all pass the same
            // whereNull check and re-notify the whole class multiple times.
            $claimed = Quiz::where('id', $quizId)->whereNull('start_reminder_sent_at')->update(['start_reminder_sent_at' => $now]);
            if ($claimed) {
                AppNotification::notifyQuizStartingSoon(Quiz::with('subject')->find($quizId));
                $startingSoonCount++;
            }
        }

        $closed = Quiz::autoCloseExpired();

        return [
            'startingSoon' => $startingSoonCount,
            'closed' => $closed,
        ];
    }
}
