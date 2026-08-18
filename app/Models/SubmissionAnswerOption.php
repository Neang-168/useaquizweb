<?php

namespace App\Models;

use App\Services\QuizGradingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionAnswerOption extends Model
{
    protected $fillable = [
        'submission_answer_id',
        'question_option_id',
    ];

    protected static function booted(): void
    {
        // These rows land after their parent SubmissionAnswer (which needs
        // to exist first for the FK), so a multiple_choice answer isn't
        // complete — and can't be correctly scored — until its selected
        // options are written too. Re-grade here for the same reason
        // SubmissionAnswer does.
        static::saved(function (SubmissionAnswerOption $selection) {
            $submission = $selection->answer?->submission;

            if ($submission && $submission->status === 'submitted') {
                app(QuizGradingService::class)->grade($submission);
            }
        });
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(SubmissionAnswer::class, 'submission_answer_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }
}
