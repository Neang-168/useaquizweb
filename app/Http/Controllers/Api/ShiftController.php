<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShiftController extends Controller
{
    use TranslatesStatus;

    /**
     * Get all shifts.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $shifts = Shift::query()
            ->when($request->input('q'), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('start_time')
            ->paginate($perPage);

        $shifts->getCollection()->transform(fn (Shift $shift) => $this->transform($shift));

        return response()->json($shifts);
    }

    /**
     * Create a new shift.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $shift = Shift::create($data);

        return response()->json([
            'message' => 'Shift created successfully.',
            'shift' => $this->transform($shift),
        ], 201);
    }

    /**
     * Get one shift.
     */
    public function show(Shift $shift)
    {
        return response()->json([
            'shift' => $this->transform($shift),
        ]);
    }

    /**
     * Update shift.
     */
    public function update(Request $request, Shift $shift)
    {
        $data = $this->validated($request, $shift);

        $shift->update($data);

        return response()->json([
            'message' => 'Shift updated successfully.',
            'shift' => $this->transform($shift),
        ]);
    }

    /**
     * Delete shift.
     */
    public function destroy(Shift $shift)
    {
        $shift->delete();

        return response()->json([
            'message' => 'Shift deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?Shift $shift = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('shifts', 'code')->ignore($shift?->id),
            ],
            'name_en' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'code' => $validated['code'],
            'name' => $validated['name_en'],
            'name_kh' => $validated['name_kh'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => $this->statusToBool($validated['status']),
        ];
    }

    private function transform(Shift $shift): array
    {
        return [
            'id' => $shift->id,
            'code' => $shift->code,
            'name_en' => $shift->name,
            'name_kh' => $shift->name_kh,
            'start_time' => substr((string) $shift->start_time, 0, 5),
            'end_time' => substr((string) $shift->end_time, 0, 5),
            'status' => $this->statusToLabel($shift->status),
        ];
    }
}
