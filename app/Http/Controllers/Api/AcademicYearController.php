<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicYearController extends Controller
{
    use TranslatesStatus;

    /**
     * Get all academic years.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $academicYears = AcademicYear::query()
            ->when($request->input('q'), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('start_date')
            ->paginate($perPage);

        $academicYears->getCollection()->transform(fn (AcademicYear $year) => $this->transform($year));

        return response()->json($academicYears);
    }

    /**
     * Create a new academic year.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $academicYear = AcademicYear::create($data);

        if ($academicYear->is_current) {
            $this->clearOtherCurrentYears($academicYear);
        }

        return response()->json([
            'message' => 'Academic year created successfully.',
            'academic_year' => $this->transform($academicYear),
        ], 201);
    }

    /**
     * Get one academic year.
     */
    public function show(AcademicYear $academicYear)
    {
        return response()->json([
            'academic_year' => $this->transform($academicYear),
        ]);
    }

    /**
     * Update academic year.
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        $data = $this->validated($request, $academicYear);

        $academicYear->update($data);

        if ($academicYear->is_current) {
            $this->clearOtherCurrentYears($academicYear);
        }

        return response()->json([
            'message' => 'Academic year updated successfully.',
            'academic_year' => $this->transform($academicYear),
        ]);
    }

    /**
     * Delete academic year.
     */
    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();

        return response()->json([
            'message' => 'Academic year deleted successfully.',
        ]);
    }

    /**
     * Mark this academic year as the current one, unsetting all others.
     */
    public function setCurrent(AcademicYear $academicYear)
    {
        $academicYear->update(['is_current' => true]);
        $this->clearOtherCurrentYears($academicYear);

        return response()->json([
            'message' => 'Academic year set as current.',
            'academic_year' => $this->transform($academicYear),
        ]);
    }

    private function clearOtherCurrentYears(AcademicYear $academicYear): void
    {
        AcademicYear::query()
            ->where('id', '!=', $academicYear->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);
    }

    private function validated(Request $request, ?AcademicYear $academicYear = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('academic_years', 'code')->ignore($academicYear?->id),
            ],
            'name_en' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'code' => $validated['code'],
            'name' => $validated['name_en'],
            'name_kh' => $validated['name_kh'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_current' => $validated['is_current'] ?? false,
            'status' => $this->statusToBool($validated['status']),
        ];
    }

    private function transform(AcademicYear $academicYear): array
    {
        return [
            'id' => $academicYear->id,
            'code' => $academicYear->code,
            'name_en' => $academicYear->name,
            'name_kh' => $academicYear->name_kh,
            'start_date' => $academicYear->start_date?->toDateString(),
            'end_date' => $academicYear->end_date?->toDateString(),
            'is_current' => $academicYear->is_current,
            'status' => $this->statusToLabel($academicYear->status),
        ];
    }
}
