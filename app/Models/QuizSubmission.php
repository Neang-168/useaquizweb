<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'student_profile_id',
        'mcq_score',
        'essay_score',
        'submitted_at',
        'status',
    ];

    protected $casts = [
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
