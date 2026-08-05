<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'name',
        'order_no',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'order_no' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'boolean',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teacherSubjects(): HasMany
    {
        return $this->hasMany(TeacherSubject::class);
    }

    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }
}