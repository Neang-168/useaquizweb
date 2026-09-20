<?php

namespace App\Http\Controllers\Api\Teacher\Concerns;

use App\Models\TeacherSubject;
use Illuminate\Support\Collection;

trait ScopesTeacherSubjects
{
    /**
     * IDs of the subjects this teacher is assigned to teach, same query
     * shape as Teacher\SubjectController::index().
     */
    protected function ownedSubjectIds(int $teacherProfileId): Collection
    {
        return TeacherSubject::query()
            ->where('teacher_profile_id', $teacherProfileId)
            ->whereNotNull('class_id')
            ->pluck('subject_id')
            ->unique();
    }
}
