<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\StudentEnrollment;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Subjects the student is taking, derived from their active class
     * enrollments and each class's assigned teacher/subject pairs.
     */
    public function index(Request $request)
    {
        $student = $request->user()->studentProfile;

        if (! $student) {
            return response()->json(['data' => []]);
        }

        $classIds = StudentEnrollment::where('student_profile_id', $student->id)
            ->where('status', 'Active')
            ->pluck('class_id')
            ->unique();

        $assignments = TeacherSubject::whereIn('class_id', $classIds)
            ->with(['subject', 'classroom.major', 'classroom.shift', 'classroom.academicYear', 'teacherProfile.user'])
            ->get()
            ->unique(fn (TeacherSubject $a) => $a->subject_id.'-'.$a->class_id);

        $quizzes = Quiz::where('status', 'Published')
            ->whereIn('class_id', $classIds)
            ->get(['id', 'subject_id', 'class_id']);

        $submittedQuizIds = QuizSubmission::where('student_profile_id', $student->id)
            ->pluck('quiz_id')
            ->unique();

        $courses = $assignments->map(function (TeacherSubject $assignment) use ($quizzes, $submittedQuizIds) {
            $subjectQuizzes = $quizzes->where('subject_id', $assignment->subject_id)->where('class_id', $assignment->class_id);

            return [
                'id' => $assignment->subject_id,
                'classId' => $assignment->class_id,
                'code' => $assignment->subject?->code,
                'title' => $assignment->subject?->name,
                'className' => $assignment->classroom?->name,
                'teacher' => $assignment->teacherProfile?->user?->full_name,
                'shift' => $assignment->classroom?->shift?->name,
                'academicYear' => $assignment->classroom?->academicYear?->name,
                'room' => $assignment->classroom?->room,
                'major' => $assignment->classroom?->major?->name,
                'totalQuizzes' => $subjectQuizzes->count(),
                'completedQuizzes' => $subjectQuizzes->pluck('id')->intersect($submittedQuizIds)->count(),
            ];
        })->values();

        return response()->json(['data' => $courses]);
    }
}
