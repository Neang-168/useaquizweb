<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('question-images:prune-tmp')->hourly();
Schedule::command('quizzes:auto-close')->everyFiveMinutes();
Schedule::command('notifications:generate-quiz-timing')->everyMinute();
