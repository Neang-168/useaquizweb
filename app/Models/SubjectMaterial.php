<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'teacher_profile_id',
        'title',
        'description',
        'duration_label',
        'file_path',
        'original_filename',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }
}
