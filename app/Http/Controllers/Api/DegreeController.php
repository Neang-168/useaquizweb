<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\Degree;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DegreeController extends Controller
{
    use TranslatesStatus;

    /**
     * Get all degrees.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $degrees = Degree::query()
            ->with('faculty')
            ->when($request->input('faculty_id'), fn ($query, $facultyId) => $query->where('faculty_id', $facultyId))
            ->when($request->input('q'), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        $degrees->getCollection()->transform(fn (Degree $degree) => $this->transform($degree));

        return response()->json($degrees);
    }

    /**
     * Create a new degree.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $degree = Degree::create($data);
        $degree->load('faculty');

        return response()->json([
            'message' => 'Degree created successfully.',
            'degree' => $this->transform($degree),
        ], 201);
    }

    /**
     * Get one degree.
     */
    public function show(Degree $degree)
    {
        $degree->load('faculty');

        return response()->json([
            'degree' => $this->transform($degree),
        ]);
    }

    /**
     * Update degree.
     */
    public function update(Request $request, Degree $degree)
    {
        $data = $this->validated($request, $degree);

        $degree->update($data);
        $degree->load('faculty');

        return response()->json([
            'message' => 'Degree updated successfully.',
            'degree' => $this->transform($degree),
        ]);
    }

    /**
     * Delete degree (soft delete).
     */
    public function destroy(Degree $degree)
    {
        $degree->delete();

        return response()->json([
            'message' => 'Degree deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?Degree $degree = null): array
    {
        $validated = $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('degrees', 'code')
                    ->where('faculty_id', $request->input('faculty_id'))
                    ->ignore($degree?->id),
            ],
            'title_en' => ['required', 'string', 'max:255'],
            'title_kh' => ['nullable', 'string', 'max:255'],
            'duration_years' => ['nullable', 'integer', 'min:1', 'max:10'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'faculty_id' => $validated['faculty_id'],
            'code' => $validated['code'],
            'name' => $validated['title_en'],
            'name_kh' => $validated['title_kh'] ?? null,
            'duration_years' => $validated['duration_years'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $this->statusToBool($validated['status'] ?? 'Active'),
        ];
    }

    private function transform(Degree $degree): array
    {
        return [
            'id' => $degree->id,
            'faculty_id' => $degree->faculty_id,
            'faculty_name' => $degree->faculty?->name,
            'code' => $degree->code,
            'title_en' => $degree->name,
            'title_kh' => $degree->name_kh,
            'duration_years' => $degree->duration_years,
            'description' => $degree->description,
            'status' => $this->statusToLabel($degree->status),
        ];
    }
}
