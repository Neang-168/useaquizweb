<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;

class EnrollmentReportController extends Controller
{
    /**
     * School-wide headcount report: every student and teacher with their
     * faculty/department/major placement, for the Admin report page to
     * filter and summarize client-side (same shape as the quiz report).
     */
    public function index()
    {
        $students = StudentProfile::query()
            ->with([
                'user',
                'enrollments' => fn ($query) => $query->latest('enrollment_date')->with(
                    'faculty', 'department', 'major', 'stage', 'shift', 'term', 'promotion', 'academicYear', 'semester'
                ),
            ])
            ->get()
            ->map(fn (StudentProfile $student) => $this->transformStudent($student))
            ->values();

        $teachers = TeacherProfile::query()
            ->with(['user', 'faculty', 'department', 'major', 'teacherSubjects.academicYear'])
            ->get()
            ->map(fn (TeacherProfile $teacher) => $this->transformTeacher($teacher))
            ->values();

        return response()->json([
            'students' => $students,
            'teachers' => $teachers,
        ]);
    }

    private function transformStudent(StudentProfile $student): array
    {
        $user = $student->user;
        $enrollment = $student->enrollments->first();

        return [
            'id' => $student->id,
            'code' => $student->student_code,
            'name' => trim(($user?->first_name ?? '').' '.($user?->last_name ?? '')),
            'username' => $user?->username,
            'gender' => $user?->gender,
            'phone' => $user?->phone,
            'dob' => $user?->dob?->toDateString(),
            'facultyId' => $enrollment?->faculty_id,
            'facultyName' => $enrollment?->faculty?->name,
            'departmentId' => $enrollment?->department_id,
            'departmentName' => $enrollment?->department?->name,
            'majorId' => $enrollment?->major_id,
            'majorName' => $enrollment?->major?->name,
            'stageId' => $enrollment?->stage_id,
            'stageName' => $enrollment?->stage?->name,
            'shiftId' => $enrollment?->shift_id,
            'shiftName' => $enrollment?->shift?->name,
            'termId' => $enrollment?->term_id,
            'termName' => $enrollment?->term?->name,
            'promotionId' => $enrollment?->promotion_id,
            'promotionName' => $enrollment?->promotion?->name
                ?: ($enrollment?->promotion ? "{$enrollment->promotion->year_start}-{$enrollment->promotion->year_end}" : null),
            'academicYearId' => $enrollment?->academic_year_id,
            'academicYearName' => $enrollment?->academicYear?->name,
            'semesterId' => $enrollment?->semester_id,
            'semesterName' => $enrollment?->semester?->name,
            'enrollmentDate' => $enrollment?->enrollment_date?->toDateString(),
            'status' => $enrollment?->status ?? ($user?->status ? 'Active' : 'Inactive'),
        ];
    }

    private function transformTeacher(TeacherProfile $teacher): array
    {
        $user = $teacher->user;

        // A teacher can be assigned across several academic years at once
        // (one per class/subject assignment), so this carries every year
        // they currently teach in rather than a single value.
        $academicYears = $teacher->teacherSubjects
            ->pluck('academicYear')
            ->filter()
            ->unique('id')
            ->values();

        return [
            'id' => $teacher->id,
            'code' => $teacher->employee_code,
            'name' => trim(($user?->first_name ?? '').' '.($user?->last_name ?? '')),
            'username' => $user?->username,
            'gender' => $user?->gender,
            'phone' => $user?->phone,
            'facultyId' => $teacher->faculty_id,
            'facultyName' => $teacher->faculty?->name,
            'departmentId' => $teacher->department_id,
            'departmentName' => $teacher->department?->name,
            'majorId' => $teacher->major_id,
            'majorName' => $teacher->major?->name,
            'academicYearIds' => $academicYears->pluck('id')->values(),
            'academicYearNames' => $academicYears->pluck('name')->implode(', ') ?: null,
            'qualification' => $teacher->qualification,
            'specialization' => $teacher->specialization,
            'hireDate' => $teacher->hire_date?->toDateString(),
            'type' => $teacher->employment_type === 'full_time' ? 'Full-Time' : 'Part-Time',
            'status' => $user?->status ? 'Active' : 'Inactive',
        ];
    }
}
