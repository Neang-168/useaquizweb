<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_profile_id',
        'subject_id',
        'class_id',
        'title',
        'description',
        'duration_minutes',
        'total_questions',
        'max_attempts',
        'shuffle_questions',
        'shuffle_options',
        'pass_mark',
        'start_at',
        'end_at',
        'status',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'total_questions' => 'integer',
        'max_attempts' => 'integer',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'pass_mark' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'quiz_questions')->withPivot('position');
    }

    public function orderedQuestions(): BelongsToMany
    {
        return $this->questions()->orderBy('quiz_questions.position');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(QuizSubmission::class);
    }
}
