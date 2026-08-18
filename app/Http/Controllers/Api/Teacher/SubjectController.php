<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Get the subjects this teacher is assigned to teach, grouped with their classes.
     */
    public function index(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            return response()->json(['data' => []]);
        }

        $assignments = TeacherSubject::query()
            ->where('teacher_profile_id', $teacher->id)
            ->whereNotNull('class_id')
            ->with(['subject.faculty', 'subject.degree', 'classroom'])
            ->get()
            ->groupBy('subject_id');

        $data = $assignments->map(function ($group) {
            $subject = $group->first()->subject;

            return [
                'id' => $subject->id,
                'code' => $subject->code,
                'name' => $subject->name,
                'credits' => $subject->credit,
                'faculty' => $subject->faculty?->name,
                'degree' => $subject->degree?->name,
                'assignedClasses' => $group->pluck('classroom.name')->filter()->unique()->values(),
            ];
        })->values();

        return response()->json(['data' => $data]);
    }
}
