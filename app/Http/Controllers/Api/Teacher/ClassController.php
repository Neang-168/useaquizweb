<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Get the classes this teacher is assigned to teach.
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
            ->with(['subject', 'classroom.major', 'classroom.shift', 'classroom.academicYear', 'classroom.stage', 'classroom.semester'])
            ->get();

        $data = $assignments->map(function (TeacherSubject $assignment) {
            $class = $assignment->classroom;

            $students = $class
                ? $class->students()
                    ->wherePivot('status', 'Active')
                    ->with('user')
                    ->get()
                    ->map(fn (StudentProfile $student) => [
                        'id' => $student->id,
                        'username' => $student->user?->username,
                        'name' => trim($student->user?->first_name . ' ' . $student->user?->last_name),
                        'name_kh' => $student->user?->name_kh,
                        'gender' => $student->user?->gender,
                        'dob' => $student->user?->dob?->format('Y-m-d'),
                        'phone' => $student->user?->phone,
                    ])
                : collect();

            return [
                'id' => $assignment->id,
                'class_id' => $class?->id,
                'className' => $class?->name,
                'major' => $class?->major?->name,
                'stage' => $class?->stage?->name,
                'semester' => $class?->semester?->name,
                'subject' => $assignment->subject?->name,
                'subject_id' => $assignment->subject_id,
                'subject_code' => $assignment->subject?->code,
                'shift' => $class?->shift?->name,
                'academicYear' => $class?->academicYear?->name,
                'room' => $class?->room,
                'totalStudents' => $students->count(),
                'students' => $students->values(),
            ];
        });

        return response()->json(['data' => $data->values()]);
    }
}
