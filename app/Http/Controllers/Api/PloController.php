<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\Plo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PloController extends Controller
{
    use TranslatesStatus;

    /**
     * Get all PLOs.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $plos = Plo::query()
            ->when($request->input('q'), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('title', 'like', "%{$q}%");
                });
            })
            ->orderBy('title')
            ->paginate($perPage);

        $plos->getCollection()->transform(fn (Plo $plo) => $this->transform($plo));

        return response()->json($plos);
    }

    /**
     * Create a new PLO.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $plo = Plo::create($data);

        return response()->json([
            'message' => 'PLO created successfully.',
            'plo' => $this->transform($plo),
        ], 201);
    }

    /**
     * Get one PLO.
     */
    public function show(Plo $plo)
    {
        return response()->json([
            'plo' => $this->transform($plo),
        ]);
    }

    /**
     * Update PLO.
     */
    public function update(Request $request, Plo $plo)
    {
        $data = $this->validated($request, $plo);

        $plo->update($data);

        return response()->json([
            'message' => 'PLO updated successfully.',
            'plo' => $this->transform($plo),
        ]);
    }

    /**
     * Delete PLO. Blocked while any CLO references it, since plo_id is a
     * restrict-on-delete FK — this turns that DB error into a clear message.
     */
    public function destroy(Plo $plo)
    {
        $cloCount = $plo->clos()->count();

        if ($cloCount > 0) {
            throw ValidationException::withMessages([
                'plo' => ["This PLO has {$cloCount} CLO(s) attached. Reassign or delete them first."],
            ]);
        }

        $plo->delete();

        return response()->json([
            'message' => 'PLO deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?Plo $plo = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('plos', 'code')->ignore($plo?->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'code' => $validated['code'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $this->statusToBool($validated['status'] ?? 'Active'),
        ];
    }

    private function transform(Plo $plo): array
    {
        return [
            'id' => $plo->id,
            'code' => $plo->code,
            'title' => $plo->title,
            'description' => $plo->description,
            'status' => $this->statusToLabel($plo->status),
        ];
    }
}
