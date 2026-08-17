<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClassroomController extends Controller
{
    use TranslatesStatus;

    /**
     * Get all classes.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $classes = Classroom::query()
            ->with('major.degree.faculty', 'faculty', 'department', 'promotion', 'stage', 'shift', 'academicYear', 'semester', 'term', 'studySession')
            ->withCount('studentEnrollments as students_count')
            ->when($request->input('major_id'), fn ($query, $id) => $query->where('major_id', $id))
            ->when($request->input('faculty_id'), fn ($query, $id) => $query->where('faculty_id', $id))
            ->when($request->input('department_id'), fn ($query, $id) => $query->where('department_id', $id))
            ->when($request->input('promotion_id'), fn ($query, $id) => $query->where('promotion_id', $id))
            ->when($request->input('stage_id'), fn ($query, $id) => $query->where('stage_id', $id))
            ->when($request->input('shift_id'), fn ($query, $id) => $query->where('shift_id', $id))
            ->when($request->input('academic_year_id'), fn ($query, $id) => $query->where('academic_year_id', $id))
            ->when($request->input('study_session_id'), fn ($query, $id) => $query->where('study_session_id', $id))
            ->when($request->input('q'), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        $classes->getCollection()->transform(fn (Classroom $class) => $this->transform($class));

        return response()->json($classes);
    }

    /**
     * Create a new class.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $class = Classroom::create($data);
        $class->load('major.degree.faculty', 'faculty', 'department', 'promotion', 'stage', 'shift', 'academicYear', 'semester', 'term', 'studySession');
        $class->loadCount('studentEnrollments as students_count');

        return response()->json([
            'message' => 'Class created successfully.',
            'class' => $this->transform($class),
        ], 201);
    }

    /**
     * Get one class.
     */
    public function show(Classroom $class)
    {
        $class->load('major.degree.faculty', 'faculty', 'department', 'promotion', 'stage', 'shift', 'academicYear', 'semester', 'term', 'studySession');
        $class->loadCount('studentEnrollments as students_count');

        return response()->json([
            'class' => $this->transform($class),
        ]);
    }

    /**
     * Update class.
     */
    public function update(Request $request, Classroom $class)
    {
        $data = $this->validated($request, $class);

        $class->update($data);
        $class->load('major.degree.faculty', 'faculty', 'department', 'promotion', 'stage', 'shift', 'academicYear', 'semester', 'term', 'studySession');
        $class->loadCount('studentEnrollments as students_count');

        return response()->json([
            'message' => 'Class updated successfully.',
            'class' => $this->transform($class),
        ]);
    }

    /**
     * Delete class.
     */
    public function destroy(Classroom $class)
    {
        $class->delete();

        return response()->json([
            'message' => 'Class deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?Classroom $class = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classes', 'code')->ignore($class?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'major_id' => ['required', 'exists:majors,id'],
            'faculty_id' => ['nullable', 'exists:faculties,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'stage_id' => ['required', 'exists:stages,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'term_id' => ['nullable', 'exists:terms,id'],
            'study_session_id' => ['nullable', 'exists:study_sessions,id'],
            'room' => ['nullable', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'code' => $validated['code'],
            'name' => $validated['name'],
            'name_kh' => $validated['name_kh'] ?? null,
            'major_id' => $validated['major_id'],
            'faculty_id' => $validated['faculty_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'promotion_id' => $validated['promotion_id'] ?? null,
            'stage_id' => $validated['stage_id'],
            'shift_id' => $validated['shift_id'],
            'academic_year_id' => $validated['academic_year_id'] ?? $this->resolveCurrentAcademicYearId(),
            'semester_id' => $validated['semester_id'] ?? null,
            'term_id' => $validated['term_id'] ?? null,
            'study_session_id' => $validated['study_session_id'] ?? null,
            'room' => $validated['room'] ?? null,
            'capacity' => $validated['capacity'],
            'status' => $this->statusToBool($validated['status']),
        ];
    }

    private function resolveCurrentAcademicYearId(): int
    {
        $academicYear = AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::orderByDesc('start_date')->first();

        if (! $academicYear) {
            throw ValidationException::withMessages([
                'academic_year_id' => ['No academic year is configured yet. Create one first.'],
            ]);
        }

        return $academicYear->id;
    }

    private function transform(Classroom $class): array
    {
        return [
            'id' => $class->id,
            'code' => $class->code,
            'name' => $class->name,
            'name_kh' => $class->name_kh,
            'major_id' => $class->major_id,
            'major_name' => $class->major?->name,
            // Legacy field kept for existing UI compatibility: historically held the major's
            // name under a misleading key, before `department_id` below was a real column.
            'department' => $class->major?->name,
            'department_id' => $class->department_id,
            'department_name' => $class->department?->name,
            'faculty_id' => $class->faculty_id ?? $class->major?->degree?->faculty_id,
            'faculty_name' => $class->faculty?->name ?? $class->major?->degree?->faculty?->name,
            'promotion_id' => $class->promotion_id,
            'promotion_name' => $class->promotion ? "{$class->promotion->year_start}-{$class->promotion->year_end}" : null,
            'study_session_id' => $class->study_session_id,
            'study_session_name' => $class->studySession?->name,
            'stage_id' => $class->stage_id,
            'stage' => $class->stage?->name,
            'shift_id' => $class->shift_id,
            'shift' => $class->shift?->name,
            'academic_year_id' => $class->academic_year_id,
            'academic_year' => $class->academicYear?->name,
            'semester_id' => $class->semester_id,
            'semester' => $class->semester?->name,
            'term_id' => $class->term_id,
            'term' => $class->term?->name,
            'room' => $class->room,
            'capacity' => $class->capacity,
            'students_count' => $class->students_count ?? 0,
            'status' => $this->statusToLabel($class->status),
        ];
    }
}
