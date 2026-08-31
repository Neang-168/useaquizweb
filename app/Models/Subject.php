<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'department_id',
        'degree_id',
        'major_id',
        'academic_year_id',
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function degree(): BelongsTo
    {
        return $this->belongsTo(Degree::class);
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teacherSubjects(): HasMany
    {
        return $this->hasMany(TeacherSubject::class);
    }

    /**
     * Auto-generate the next subject code for a faculty, e.g. "SCT101",
     * "SCT102", so the create form can prefill it before saving.
     */
    public static function generateCode(string $facultyCode): string
    {
        $prefix = strtoupper($facultyCode);

        $maxNumber = static::where('code', 'like', "{$prefix}%")
            ->pluck('code')
            ->map(function ($code) use ($prefix) {
                $suffix = substr($code, strlen($prefix));
                return ctype_digit($suffix) ? (int) $suffix : 0;
            })
            ->max();

        $next = $maxNumber ? $maxNumber + 1 : 101;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}