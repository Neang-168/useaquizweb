<?php

namespace App\Console\Commands;

use App\Services\QuizTimingNotifier;
use Illuminate\Console\Command;

class GenerateQuizTimingNotifications extends Command
{
    protected $signature = 'notifications:generate-quiz-timing';

    protected $description = 'Notify enrolled students ~5 minutes before a published quiz starts';

    public function handle(): int
    {
        $result = QuizTimingNotifier::sweep();

        $this->info(sprintf(
            'Notified %d starting-soon quiz(zes). Auto-closed %d expired quiz(zes).',
            $result['startingSoon'],
            $result['closed']
        ));

        return self::SUCCESS;
    }
}
