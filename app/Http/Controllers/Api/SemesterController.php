<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SemesterController extends Controller
{
    use TranslatesStatus;

    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $semesters = Semester::query()
            ->with('academicYear')
            ->when($request->input('academic_year_id'), fn ($query, $id) => $query->where('academic_year_id', $id))
            ->orderBy('order_no')
            ->paginate($perPage);

        $semesters->getCollection()->transform(fn (Semester $semester) => $this->transform($semester));

        return response()->json($semesters);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $semester = Semester::create($data);
        $semester->load('academicYear');

        return response()->json([
            'message' => 'Semester created successfully.',
            'semester' => $this->transform($semester),
        ], 201);
    }

    public function show(Semester $semester)
    {
        $semester->load('academicYear');

        return response()->json([
            'semester' => $this->transform($semester),
        ]);
    }

    public function update(Request $request, Semester $semester)
    {
        $data = $this->validated($request, $semester);

        $semester->update($data);
        $semester->load('academicYear');

        return response()->json([
            'message' => 'Semester updated successfully.',
            'semester' => $this->transform($semester),
        ]);
    }

    public function destroy(Semester $semester)
    {
        $semester->delete();

        return response()->json([
            'message' => 'Semester deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?Semester $semester = null): array
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name_en' => ['required', 'string', 'max:255'],
            'order_no' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['nullable', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'academic_year_id' => $validated['academic_year_id'],
            'name' => $validated['name_en'],
            'order_no' => $validated['order_no'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $this->statusToBool($validated['status'] ?? 'Active'),
        ];
    }

    private function transform(Semester $semester): array
    {
        return [
            'id' => $semester->id,
            'academic_year_id' => $semester->academic_year_id,
            'academic_year' => $semester->academicYear?->name,
            'name' => $semester->name,
            'name_en' => $semester->academicYear
                ? "{$semester->name} ({$semester->academicYear->name})"
                : $semester->name,
            'order_no' => $semester->order_no,
            'start_date' => $semester->start_date?->toDateString(),
            'end_date' => $semester->end_date?->toDateString(),
            'status' => $this->statusToLabel($semester->status),
        ];
    }
}
