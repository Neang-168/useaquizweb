<?php

namespace App\Models;

use App\Services\QuizGradingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizSubmission extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // Auto-grade the moment a submission is (or becomes) "submitted".
        // Whatever eventually builds the student-taking flow just needs to
        // write the raw answers and save the submission with this status —
        // grading itself needs no separate call. QuizGradingService saves
        // its results with saveQuietly(), so it can't re-trigger this.
        static::saved(function (QuizSubmission $submission) {
            if ($submission->status !== 'submitted') {
                return;
            }

            if (! $submission->wasRecentlyCreated && ! $submission->wasChanged('status')) {
                return;
            }

            app(QuizGradingService::class)->grade($submission);
        });
    }

    protected $fillable = [
        'quiz_id',
        'student_profile_id',
        'attempt_number',
        'mcq_score',
        'essay_score',
        'submitted_at',
        'status',
    ];

    protected $casts = [
        'attempt_number' => 'integer',
        'mcq_score' => 'integer',
        'essay_score' => 'integer',
        'submitted_at' => 'datetime',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SubmissionAnswer::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}
