<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_code',
        'admission_date',
    ];

    protected $casts = [
        'admission_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /**
     * Every class this student is assigned to. A student can belong to
     * many classes at once (e.g. retaking a course alongside their main
     * cohort), so this is a proper many-to-many via `class_student`.
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'class_student', 'student_profile_id', 'class_id')
            ->withPivot('status')
            ->withTimestamps();
    }
}