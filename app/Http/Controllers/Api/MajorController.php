<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MajorController extends Controller
{
    use TranslatesStatus;

    /**
     * Get all majors.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $majors = Major::query()
            ->with('degree.faculty')
            ->when($request->input('degree_id'), fn ($query, $degreeId) => $query->where('degree_id', $degreeId))
            ->when($request->input('faculty_id'), fn ($query, $facultyId) => $query->whereHas('degree', fn ($query) => $query->where('faculty_id', $facultyId)))
            ->when($request->input('q'), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        $majors->getCollection()->transform(fn (Major $major) => $this->transform($major));

        return response()->json($majors);
    }

    /**
     * Create a new major.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $major = Major::create($data);
        $major->load('degree.faculty');

        return response()->json([
            'message' => 'Major created successfully.',
            'major' => $this->transform($major),
        ], 201);
    }

    /**
     * Get one major.
     */
    public function show(Major $major)
    {
        $major->load('degree.faculty');

        return response()->json([
            'major' => $this->transform($major),
        ]);
    }

    /**
     * Update major.
     */
    public function update(Request $request, Major $major)
    {
        $data = $this->validated($request, $major);

        $major->update($data);
        $major->load('degree.faculty');

        return response()->json([
            'message' => 'Major updated successfully.',
            'major' => $this->transform($major),
        ]);
    }

    /**
     * Delete major (soft delete).
     */
    public function destroy(Major $major)
    {
        $major->delete();

        return response()->json([
            'message' => 'Major deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?Major $major = null): array
    {
        $validated = $request->validate([
            'degree_id' => ['required', 'exists:degrees,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('majors', 'code')
                    ->where('degree_id', $request->input('degree_id'))
                    ->ignore($major?->id),
            ],
            'name_en' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'degree_id' => $validated['degree_id'],
            'code' => $validated['code'],
            'name' => $validated['name_en'],
            'name_kh' => $validated['name_kh'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $this->statusToBool($validated['status'] ?? 'Active'),
        ];
    }

    private function transform(Major $major): array
    {
        return [
            'id' => $major->id,
            'degree_id' => $major->degree_id,
            'degree_title' => $major->degree?->name,
            'faculty_id' => $major->degree?->faculty_id,
            'faculty_name' => $major->degree?->faculty?->name,
            'code' => $major->code,
            'name_en' => $major->name,
            'name_kh' => $major->name_kh,
            'description' => $major->description,
            'status' => $this->statusToLabel($major->status),
        ];
    }
}
