<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeacherAssignmentController extends Controller
{
    /**
     * Get all teacher-subject-class assignments (optionally filtered by teacher).
     */
    public function index(Request $request)
    {
        $assignments = TeacherSubject::query()
            ->with(['teacherProfile.user', 'subject', 'classroom'])
            ->when($request->input('teacher_profile_id'), fn ($query, $id) => $query->where('teacher_profile_id', $id))
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $assignments->map(fn (TeacherSubject $assignment) => $this->transform($assignment)),
        ]);
    }

    /**
     * Assign a teacher to teach a subject for a specific class.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_profile_id' => ['required', 'exists:teacher_profiles,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'exists:classes,id'],
        ]);

        $class = Classroom::with('major.degree')->findOrFail($validated['class_id']);
        $subject = Subject::findOrFail($validated['subject_id']);

        $classFacultyId = $class->major?->degree?->faculty_id;

        if ($classFacultyId && $subject->faculty_id !== $classFacultyId) {
            throw ValidationException::withMessages([
                'subject_id' => ['This subject belongs to a different faculty than the selected class.'],
            ]);
        }

        $exists = TeacherSubject::where('teacher_profile_id', $validated['teacher_profile_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('class_id', $validated['class_id'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'class_id' => ['This teacher is already assigned to this subject for this class.'],
            ]);
        }

        $assignment = TeacherSubject::create([
            'teacher_profile_id' => $validated['teacher_profile_id'],
            'subject_id' => $validated['subject_id'],
            'class_id' => $class->id,
            'academic_year_id' => $class->academic_year_id,
            'semester_id' => $class->semester_id,
            'shift_id' => $class->shift_id,
        ]);

        $assignment->load(['teacherProfile.user', 'subject', 'classroom']);

        return response()->json([
            'message' => 'Teacher assigned successfully.',
            'assignment' => $this->transform($assignment),
        ], 201);
    }

    /**
     * Remove a teacher-subject-class assignment.
     */
    public function destroy(TeacherSubject $teacherAssignment)
    {
        $teacherAssignment->delete();

        return response()->json([
            'message' => 'Assignment removed successfully.',
        ]);
    }

    private function transform(TeacherSubject $assignment): array
    {
        return [
            'id' => $assignment->id,
            'teacher_profile_id' => $assignment->teacher_profile_id,
            'teacher_name' => trim($assignment->teacherProfile?->user?->first_name . ' ' . $assignment->teacherProfile?->user?->last_name),
            'subject_id' => $assignment->subject_id,
            'subject_name' => $assignment->subject?->name,
            'subject_code' => $assignment->subject?->code,
            'class_id' => $assignment->class_id,
            'class_name' => $assignment->classroom?->name,
        ];
    }
}
