<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SubjectMaterial;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SubjectController extends Controller
{
    /**
     * Get the subjects this teacher is assigned to teach, grouped with their classes & materials.
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

        $materials = SubjectMaterial::query()
            ->where('teacher_profile_id', $teacher->id)
            ->whereIn('subject_id', $assignments->keys())
            ->orderBy('created_at')
            ->get()
            ->groupBy('subject_id');

        $data = $assignments->map(function ($group) use ($materials) {
            $subject = $group->first()->subject;

            return [
                'id' => $subject->id,
                'code' => $subject->code,
                'name' => $subject->name,
                'credits' => $subject->credit,
                'faculty' => $subject->faculty?->name,
                'degree' => $subject->degree?->name,
                'assignedClasses' => $group->pluck('classroom.name')->filter()->unique()->values(),
                'chapters' => ($materials->get($subject->id) ?? collect())->map(fn (SubjectMaterial $material) => [
                    'id' => $material->id,
                    'title' => $material->title,
                    'description' => $material->description,
                    'duration' => $material->duration_label,
                    'resourceName' => $material->original_filename,
                    'fileUrl' => $material->file_path ? Storage::disk('public')->url($material->file_path) : null,
                ])->values(),
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    /**
     * Add a syllabus chapter / material (optionally with a file) to one of this teacher's subjects.
     */
    public function storeMaterial(Request $request, int $subject)
    {
        $teacher = $request->user()->teacherProfile;

        $this->assertOwnsSubject($teacher?->id, $subject);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_label' => ['nullable', 'string', 'max:50'],
            'file' => ['nullable', 'file', 'mimes:pdf,ppt,pptx,doc,docx', 'max:20480'],
        ]);

        $filePath = null;
        $originalFilename = null;

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('subject-materials', 'public');
            $originalFilename = $request->file('file')->getClientOriginalName();
        }

        $material = SubjectMaterial::create([
            'subject_id' => $subject,
            'teacher_profile_id' => $teacher->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_label' => $validated['duration_label'] ?? null,
            'file_path' => $filePath,
            'original_filename' => $originalFilename,
        ]);

        return response()->json([
            'message' => 'Material added successfully.',
            'material' => [
                'id' => $material->id,
                'title' => $material->title,
                'description' => $material->description,
                'duration' => $material->duration_label,
                'resourceName' => $material->original_filename,
                'fileUrl' => $material->file_path ? Storage::disk('public')->url($material->file_path) : null,
            ],
        ], 201);
    }

    /**
     * Remove a material belonging to this teacher.
     */
    public function destroyMaterial(Request $request, SubjectMaterial $material)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher || $material->teacher_profile_id !== $teacher->id) {
            abort(403);
        }

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return response()->json(['message' => 'Material removed successfully.']);
    }

    private function assertOwnsSubject(?int $teacherId, int $subjectId): void
    {
        $owns = $teacherId && TeacherSubject::where('teacher_profile_id', $teacherId)
            ->where('subject_id', $subjectId)
            ->whereNotNull('class_id')
            ->exists();

        if (! $owns) {
            throw ValidationException::withMessages([
                'subject_id' => ['You are not assigned to teach this subject.'],
            ]);
        }
    }
}
