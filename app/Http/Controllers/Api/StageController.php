<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StageController extends Controller
{
    use TranslatesStatus;

    /**
     * Get all stages.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $stages = Stage::query()
            ->when($request->input('q'), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('order_no')
            ->paginate($perPage);

        $stages->getCollection()->transform(fn (Stage $stage) => $this->transform($stage));

        return response()->json($stages);
    }

    /**
     * Create a new stage.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $stage = Stage::create($data);

        return response()->json([
            'message' => 'Stage created successfully.',
            'stage' => $this->transform($stage),
        ], 201);
    }

    /**
     * Get one stage.
     */
    public function show(Stage $stage)
    {
        return response()->json([
            'stage' => $this->transform($stage),
        ]);
    }

    /**
     * Update stage.
     */
    public function update(Request $request, Stage $stage)
    {
        $data = $this->validated($request, $stage);

        $stage->update($data);

        return response()->json([
            'message' => 'Stage updated successfully.',
            'stage' => $this->transform($stage),
        ]);
    }

    /**
     * Delete stage.
     */
    public function destroy(Stage $stage)
    {
        $stage->delete();

        return response()->json([
            'message' => 'Stage deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?Stage $stage = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('stages', 'code')->ignore($stage?->id),
            ],
            'name_en' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'level' => ['required', 'integer', 'min:1', 'max:20'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'code' => $validated['code'],
            'name' => $validated['name_en'],
            'name_kh' => $validated['name_kh'] ?? null,
            'order_no' => $validated['level'],
            'status' => $this->statusToBool($validated['status']),
        ];
    }

    private function transform(Stage $stage): array
    {
        return [
            'id' => $stage->id,
            'code' => $stage->code,
            'name_en' => $stage->name,
            'name_kh' => $stage->name_kh,
            'level' => $stage->order_no,
            'status' => $this->statusToLabel($stage->status),
        ];
    }
}
