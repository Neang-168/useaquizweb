<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_profile_id',
        'subject_id',
        'type',
        'title',
        'image_path',
        'image_alt',
        'difficulty',
        'points',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    protected static function booted(): void
    {
        // FK cascades drop the option/pair rows in the DB, but the image
        // files behind them are only reachable from here first.
        static::deleting(function (Question $question) {
            $paths = collect([$question->image_path])
                ->merge($question->options()->pluck('image_path'))
                ->merge($question->matchingPairs()->pluck('left_image_path'))
                ->merge($question->matchingPairs()->pluck('right_image_path'))
                ->filter()
                ->all();

            if ($paths) {
                Storage::disk('public')->delete($paths);
            }
        });
    }

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function matchingPairs(): HasMany
    {
        return $this->hasMany(QuestionMatchingPair::class);
    }

    public function quizzes(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class, 'quiz_questions')->withPivot('position');
    }
}
