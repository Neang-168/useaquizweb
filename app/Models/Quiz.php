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
        'total_score',
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
        'total_score' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'start_reminder_sent_at' => 'datetime',
        'end_reminder_sent_at' => 'datetime',
        'end_hour_reminder_sent_at' => 'datetime',
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
        return $this->belongsToMany(Question::class, 'quiz_questions')->withPivot('position', 'points');
    }

    public function orderedQuestions(): BelongsToMany
    {
        return $this->questions()->orderBy('quiz_questions.position');
    }

    /**
     * A question's points as they count for this quiz: the per-quiz override
     * set when the teacher scaled questions to a Total Score, falling back to
     * the question's own points in the bank if this quiz never set one.
     */
    public function pointsFor(Question $question): int
    {
        return (int) ($question->pivot->points ?? $question->points);
    }

    /**
     * Sum of pointsFor() across a loaded questions collection. Callers must
     * eager-load 'questions' first — this does not query.
     */
    public function computeTotalPoints(): int
    {
        return (int) $this->questions->sum(fn (Question $q) => $this->pointsFor($q));
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(QuizSubmission::class);
    }

    /**
     * Flip every Published quiz whose end_at has passed to Closed. Idempotent —
     * safe to call from the scheduled command and also inline from read paths
     * (quiz/calendar/dashboard listings) so a quiz closes on time even in an
     * environment where the scheduler isn't wired to a system cron.
     */
    public static function autoCloseExpired(): int
    {
        return static::where('status', 'Published')
            ->whereNotNull('end_at')
            ->where('end_at', '<', now())
            ->update(['status' => 'Closed']);
    }
}
