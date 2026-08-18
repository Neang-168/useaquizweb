<?php

namespace App\Models;

use App\Services\QuizGradingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionAnswerMatch extends Model
{
    protected $fillable = [
        'submission_answer_id',
        'left_pair_id',
        'selected_right_pair_id',
    ];

    protected static function booted(): void
    {
        // Same reasoning as SubmissionAnswerOption: a matching answer's
        // pairs land after its parent SubmissionAnswer, so it can't be
        // scored correctly until they're written too.
        static::saved(function (SubmissionAnswerMatch $match) {
            $submission = $match->answer?->submission;

            if ($submission && $submission->status === 'submitted') {
                app(QuizGradingService::class)->grade($submission);
            }
        });
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(SubmissionAnswer::class, 'submission_answer_id');
    }

    public function leftPair(): BelongsTo
    {
        return $this->belongsTo(QuestionMatchingPair::class, 'left_pair_id');
    }

    public function selectedRightPair(): BelongsTo
    {
        return $this->belongsTo(QuestionMatchingPair::class, 'selected_right_pair_id');
    }
}
