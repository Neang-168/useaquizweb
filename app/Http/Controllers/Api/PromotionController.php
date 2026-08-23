<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PromotionController extends Controller
{
    use TranslatesStatus;

    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $promotions = Promotion::query()
            ->when($request->input('q'), function ($query, $q) {
                $query->where('year_start', 'like', "%{$q}%")
                    ->orWhere('year_end', 'like', "%{$q}%");
            })
            ->orderByDesc('year_start')
            ->paginate($perPage);

        $promotions->getCollection()->transform(fn (Promotion $promotion) => $this->transform($promotion));

        return response()->json($promotions);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $promotion = Promotion::create($data);

        return response()->json([
            'message' => 'Generation created successfully.',
            'promotion' => $this->transform($promotion),
        ], 201);
    }

    public function show(Promotion $promotion)
    {
        return response()->json([
            'promotion' => $this->transform($promotion),
        ]);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $this->validated($request, $promotion);

        $promotion->update($data);

        return response()->json([
            'message' => 'Generation updated successfully.',
            'promotion' => $this->transform($promotion),
        ]);
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return response()->json([
            'message' => 'Generation deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?Promotion $promotion = null): array
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'year_start' => ['required', 'integer', 'min:2000', 'max:2100'],
            'year_end' => ['required', 'integer', 'min:2000', 'max:2100', 'gt:year_start'],
            'status' => ['nullable', Rule::in(['Active', 'Inactive'])],
        ]);

        $duplicate = Promotion::query()
            ->where('year_start', $validated['year_start'])
            ->where('year_end', $validated['year_end'])
            ->when($promotion, fn ($query) => $query->where('id', '!=', $promotion->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'year_start' => ['A generation for these years already exists.'],
            ]);
        }

        return [
            'name' => $validated['name'] ?? null,
            'name_kh' => $validated['name_kh'] ?? null,
            'year_start' => $validated['year_start'],
            'year_end' => $validated['year_end'],
            'status' => $this->statusToBool($validated['status'] ?? 'Active'),
        ];
    }

    private function transform(Promotion $promotion): array
    {
        return [
            'id' => $promotion->id,
            'year_start' => $promotion->year_start,
            'year_end' => $promotion->year_end,
            'name_en' => $promotion->name ?: "{$promotion->year_start}-{$promotion->year_end}",
            'name_kh' => $promotion->name_kh,
            'status' => $this->statusToLabel($promotion->status),
        ];
    }
}
