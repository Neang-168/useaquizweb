<?php

namespace App\Models;

use App\Services\QuizGradingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmissionAnswer extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // A submission row must exist (FK) before any answer can be
        // written to it, so by the time a real submit flow saves the
        // submission with status "submitted", its answers don't exist yet
        // — QuizSubmission's own save-observer alone would grade an empty
        // answer set. Re-grading here too, as each answer lands, means the
        // score is correct by the time the last one is written, regardless
        // of insert order. QuizGradingService saves quietly, so this can't
        // recurse into itself.
        static::saved(function (SubmissionAnswer $answer) {
            $submission = $answer->submission;

            if ($submission && $submission->status === 'submitted') {
                app(QuizGradingService::class)->grade($submission);
            }
        });
    }

    protected $fillable = [
        'quiz_submission_id',
        'question_id',
        'selected_option_id',
        'essay_answer_text',
        'is_correct',
        'awarded_score',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'awarded_score' => 'integer',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(QuizSubmission::class, 'quiz_submission_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }

    /**
     * The set of options picked for a multiple_choice answer (supports
     * more than one, unlike selected_option_id).
     */
    public function selectedOptions(): HasMany
    {
        return $this->hasMany(SubmissionAnswerOption::class);
    }

    /**
     * The student's left-to-right pairing for a matching answer.
     */
    public function matches(): HasMany
    {
        return $this->hasMany(SubmissionAnswerMatch::class);
    }
}
