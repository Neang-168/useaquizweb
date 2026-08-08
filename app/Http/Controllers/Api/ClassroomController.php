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
            ->with('major', 'stage', 'shift', 'academicYear')
            ->withCount('studentEnrollments as students_count')
            ->when($request->input('major_id'), fn ($query, $id) => $query->where('major_id', $id))
            ->when($request->input('stage_id'), fn ($query, $id) => $query->where('stage_id', $id))
            ->when($request->input('shift_id'), fn ($query, $id) => $query->where('shift_id', $id))
            ->when($request->input('academic_year_id'), fn ($query, $id) => $query->where('academic_year_id', $id))
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
        $class->load('major', 'stage', 'shift', 'academicYear');
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
        $class->load('major', 'stage', 'shift', 'academicYear');
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
        $class->load('major', 'stage', 'shift', 'academicYear');
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
            'major_id' => ['required', 'exists:majors,id'],
            'stage_id' => ['required', 'exists:stages,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'room' => ['nullable', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'code' => $validated['code'],
            'name' => $validated['name'],
            'major_id' => $validated['major_id'],
            'stage_id' => $validated['stage_id'],
            'shift_id' => $validated['shift_id'],
            'academic_year_id' => $validated['academic_year_id'] ?? $this->resolveCurrentAcademicYearId(),
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
            'major_id' => $class->major_id,
            'department' => $class->major?->name,
            'stage_id' => $class->stage_id,
            'stage' => $class->stage?->name,
            'shift_id' => $class->shift_id,
            'shift' => $class->shift?->name,
            'academic_year_id' => $class->academic_year_id,
            'academic_year' => $class->academicYear?->name,
            'room' => $class->room,
            'capacity' => $class->capacity,
            'students_count' => $class->students_count ?? 0,
            'status' => $this->statusToLabel($class->status),
        ];
    }
}
