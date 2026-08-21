<?php

namespace App\Console\Commands;

use App\Services\QuizTimingNotifier;
use Illuminate\Console\Command;

class GenerateQuizTimingNotifications extends Command
{
    protected $signature = 'notifications:generate-quiz-timing';

    protected $description = 'Notify enrolled students ~1 minute before a published quiz starts, ~1 hour before it closes, and ~1 minute before it closes';

    public function handle(): int
    {
        $result = QuizTimingNotifier::sweep();

        $this->info(sprintf(
            'Notified %d starting-soon, %d closing-in-1-hour, and %d closing-soon quiz(zes). Auto-closed %d expired quiz(zes).',
            $result['startingSoon'],
            $result['closingInOneHour'],
            $result['endingSoon'],
            $result['closed']
        ));

        return self::SUCCESS;
    }
}
