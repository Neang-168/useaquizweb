<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacultyController extends Controller
{
    use TranslatesStatus;

    /**
     * Get all faculties.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $faculties = Faculty::query()
            ->when($request->input('q'), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('name_kh', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        $faculties->getCollection()->transform(fn (Faculty $faculty) => $this->transform($faculty));

        return response()->json($faculties);
    }

    /**
     * Create a new faculty.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $faculty = Faculty::create($data);

        return response()->json([
            'message' => 'Faculty created successfully.',
            'faculty' => $this->transform($faculty),
        ], 201);
    }

    /**
     * Get one faculty.
     */
    public function show(Faculty $faculty)
    {
        return response()->json([
            'faculty' => $this->transform($faculty),
        ]);
    }

    /**
     * Update faculty.
     */
    public function update(Request $request, Faculty $faculty)
    {
        $data = $this->validated($request, $faculty);

        $faculty->update($data);

        return response()->json([
            'message' => 'Faculty updated successfully.',
            'faculty' => $this->transform($faculty),
        ]);
    }

    /**
     * Delete faculty (soft delete).
     */
    public function destroy(Faculty $faculty)
    {
        $faculty->delete();

        return response()->json([
            'message' => 'Faculty deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?Faculty $faculty = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('faculties', 'code')->ignore($faculty?->id),
            ],
            'name_en' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'code' => $validated['code'],
            'name' => $validated['name_en'],
            'name_kh' => $validated['name_kh'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $this->statusToBool($validated['status']),
        ];
    }

    private function transform(Faculty $faculty): array
    {
        return [
            'id' => $faculty->id,
            'code' => $faculty->code,
            'name_en' => $faculty->name,
            'name_kh' => $faculty->name_kh,
            'description' => $faculty->description,
            'status' => $this->statusToLabel($faculty->status),
        ];
    }
}
