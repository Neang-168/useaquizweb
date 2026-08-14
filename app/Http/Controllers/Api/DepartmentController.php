<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    use TranslatesStatus;

    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $departments = Department::query()
            ->with('faculty')
            ->when($request->input('faculty_id'), fn ($query, $facultyId) => $query->where('faculty_id', $facultyId))
            ->when($request->input('q'), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('name_kh', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        $departments->getCollection()->transform(fn (Department $department) => $this->transform($department));

        return response()->json($departments);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $department = Department::create($data);
        $department->load('faculty');

        return response()->json([
            'message' => 'Department created successfully.',
            'department' => $this->transform($department),
        ], 201);
    }

    public function show(Department $department)
    {
        $department->load('faculty');

        return response()->json([
            'department' => $this->transform($department),
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $data = $this->validated($request, $department);

        $department->update($data);
        $department->load('faculty');

        return response()->json([
            'message' => 'Department updated successfully.',
            'department' => $this->transform($department),
        ]);
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return response()->json([
            'message' => 'Department deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?Department $department = null): array
    {
        $validated = $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('departments', 'code')->ignore($department?->id),
            ],
            'name_en' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'faculty_id' => $validated['faculty_id'],
            'code' => $validated['code'],
            'name' => $validated['name_en'],
            'name_kh' => $validated['name_kh'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $this->statusToBool($validated['status'] ?? 'Active'),
        ];
    }

    private function transform(Department $department): array
    {
        return [
            'id' => $department->id,
            'faculty_id' => $department->faculty_id,
            'faculty_name' => $department->faculty?->name,
            'code' => $department->code,
            'name_en' => $department->name,
            'name_kh' => $department->name_kh,
            'description' => $department->description,
            'status' => $this->statusToLabel($department->status),
        ];
    }
}
