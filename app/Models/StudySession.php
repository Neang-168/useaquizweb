<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudySession extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'degree_id',
        'major_id',
        'code',
        'name',
        'name_kh',
        'credit',
        'description',
        'status',
    ];

    protected $casts = [
        'credit' => 'integer',
        'status' => 'boolean',
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function degree(): BelongsTo
    {
        return $this->belongsTo(Degree::class);
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(Classroom::class, 'study_session_id');
    }
}
