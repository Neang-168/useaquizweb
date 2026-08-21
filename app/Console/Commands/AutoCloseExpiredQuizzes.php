<?php

namespace App\Console\Commands;

use App\Models\Quiz;
use Illuminate\Console\Command;

class AutoCloseExpiredQuizzes extends Command
{
    protected $signature = 'quizzes:auto-close';

    protected $description = 'Close published quizzes whose availability window has passed';

    public function handle(): int
    {
        $closed = Quiz::autoCloseExpired();

        $this->info("Closed {$closed} expired quiz(zes).");

        return self::SUCCESS;
    }
}
