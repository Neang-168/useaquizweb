<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\StudentEnrollment;
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
            ->with(['subject', 'classroom.major', 'classroom.shift', 'classroom.academicYear'])
            ->get();

        $data = $assignments->map(function (TeacherSubject $assignment) {
            $class = $assignment->classroom;

            $students = StudentEnrollment::query()
                ->where('class_id', $class?->id)
                ->where('status', 'Active')
                ->with('studentProfile.user')
                ->get()
                ->map(fn (StudentEnrollment $enrollment) => [
                    'id' => $enrollment->id,
                    'student_id' => $enrollment->studentProfile?->student_code,
                    'name' => trim($enrollment->studentProfile?->user?->first_name . ' ' . $enrollment->studentProfile?->user?->last_name),
                    'gender' => $enrollment->studentProfile?->user?->gender,
                    'email' => $enrollment->studentProfile?->user?->email,
                ]);

            return [
                'id' => $assignment->id,
                'class_id' => $class?->id,
                'className' => $class?->name,
                'major' => $class?->major?->name,
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
