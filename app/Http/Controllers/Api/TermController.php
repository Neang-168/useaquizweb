<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TermController extends Controller
{
    use TranslatesStatus;

    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $terms = Term::query()
            ->with('academicYear')
            ->when($request->input('academic_year_id'), fn ($query, $id) => $query->where('academic_year_id', $id))
            ->when($request->input('q'), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('order_no')
            ->paginate($perPage);

        $terms->getCollection()->transform(fn (Term $term) => $this->transform($term));

        return response()->json($terms);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $term = Term::create($data);
        $term->load('academicYear');

        return response()->json([
            'message' => 'Term created successfully.',
            'term' => $this->transform($term),
        ], 201);
    }

    public function show(Term $term)
    {
        $term->load('academicYear');

        return response()->json([
            'term' => $this->transform($term),
        ]);
    }

    public function update(Request $request, Term $term)
    {
        $data = $this->validated($request, $term);

        $term->update($data);
        $term->load('academicYear');

        return response()->json([
            'message' => 'Term updated successfully.',
            'term' => $this->transform($term),
        ]);
    }

    public function destroy(Term $term)
    {
        $term->delete();

        return response()->json([
            'message' => 'Term deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?Term $term = null): array
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('terms', 'code')->ignore($term?->id),
            ],
            'name_en' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'order_no' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['nullable', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'academic_year_id' => $validated['academic_year_id'],
            'code' => $validated['code'] ?? null,
            'name' => $validated['name_en'],
            'name_kh' => $validated['name_kh'] ?? null,
            'order_no' => $validated['order_no'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $this->statusToBool($validated['status'] ?? 'Active'),
        ];
    }

    private function transform(Term $term): array
    {
        return [
            'id' => $term->id,
            'academic_year_id' => $term->academic_year_id,
            'academic_year' => $term->academicYear?->name,
            'code' => $term->code,
            'name_en' => $term->name,
            'name_kh' => $term->name_kh,
            'order_no' => $term->order_no,
            'start_date' => $term->start_date?->toDateString(),
            'end_date' => $term->end_date?->toDateString(),
            'status' => $this->statusToLabel($term->status),
        ];
    }
}
