<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Api\Teacher\Concerns\ScopesTeacherSubjects;
use App\Http\Controllers\Controller;
use App\Models\Clo;
use App\Models\Llo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LloController extends Controller
{
    use ScopesTeacherSubjects, TranslatesStatus;

    /**
     * Get this teacher's LLOs, optionally scoped to a subject or a CLO.
     */
    public function index(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            return response()->json(['data' => []]);
        }

        $ownedSubjectIds = $this->ownedSubjectIds($teacher->id);

        $llos = Llo::query()
            ->whereHas('clo', fn ($query) => $query->whereIn('subject_id', $ownedSubjectIds))
            ->with(['clo.subject', 'creator'])
            ->when($request->input('clo_id'), fn ($query, $id) => $query->where('clo_id', $id))
            ->when($request->input('subject_id'), fn ($query, $id) => $query->whereHas('clo', fn ($q) => $q->where('subject_id', $id)))
            ->when(! $request->boolean('include_inactive'), fn ($query) => $query->where('status', true))
            ->orderBy('title')
            ->get();

        return response()->json([
            'data' => $llos->map(fn (Llo $llo) => $this->transform($llo)),
        ]);
    }

    /**
     * Create a new LLO.
     */
    public function store(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            abort(403);
        }

        $data = $this->validated($request, $teacher->id);
        $data['created_by'] = $request->user()->id;

        $llo = Llo::create($data);
        $llo->load('clo.subject', 'creator');

        return response()->json([
            'message' => 'LLO created successfully.',
            'llo' => $this->transform($llo),
        ], 201);
    }

    /**
     * Get one LLO.
     */
    public function show(Request $request, Llo $llo)
    {
        $this->authorizeOwner($request, $llo);

        $llo->load('clo.subject', 'creator');

        return response()->json([
            'llo' => $this->transform($llo),
        ]);
    }

    /**
     * Update LLO.
     */
    public function update(Request $request, Llo $llo)
    {
        $this->authorizeOwner($request, $llo);

        $teacher = $request->user()->teacherProfile;
        $data = $this->validated($request, $teacher->id, $llo);

        $llo->update($data);
        $llo->load('clo.subject', 'creator');

        return response()->json([
            'message' => 'LLO updated successfully.',
            'llo' => $this->transform($llo),
        ]);
    }

    /**
     * Delete LLO. Blocked while any question is tagged to it, so retagging
     * is a deliberate teacher action rather than a silent null-out (even
     * though the DB FK is nullOnDelete and would tolerate it).
     */
    public function destroy(Request $request, Llo $llo)
    {
        $this->authorizeOwner($request, $llo);

        $questionCount = $llo->questions()->count();

        if ($questionCount > 0) {
            throw ValidationException::withMessages([
                'llo' => ["This LLO is tagged on {$questionCount} question(s). Retag them first."],
            ]);
        }

        $llo->delete();

        return response()->json(['message' => 'LLO deleted successfully.']);
    }

    private function authorizeOwner(Request $request, Llo $llo): void
    {
        $teacher = $request->user()->teacherProfile;

        $subjectId = $llo->clo?->subject_id;

        if (! $teacher || ! $subjectId || ! $this->ownedSubjectIds($teacher->id)->contains($subjectId)) {
            abort(403);
        }
    }

    private function validated(Request $request, int $teacherProfileId, ?Llo $llo = null): array
    {
        $ownedSubjectIds = $this->ownedSubjectIds($teacherProfileId);
        $ownedCloIds = Clo::whereIn('subject_id', $ownedSubjectIds)->pluck('id');

        $validated = $request->validate([
            'clo_id' => [
                'required',
                'exists:clos,id',
                Rule::in($ownedCloIds->all()),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('llos', 'code')
                    ->where('clo_id', $request->input('clo_id'))
                    ->ignore($llo?->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'bloom_level' => ['required', Rule::in(['Remember', 'Understand', 'Apply', 'Analyze', 'Evaluate', 'Create'])],
            'lesson_no' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'clo_id' => $validated['clo_id'],
            'code' => $validated['code'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'bloom_level' => $validated['bloom_level'],
            'lesson_no' => $validated['lesson_no'] ?? null,
            'status' => $this->statusToBool($validated['status'] ?? 'Active'),
        ];
    }

    private function transform(Llo $llo): array
    {
        return [
            'id' => $llo->id,
            'clo_id' => $llo->clo_id,
            'clo_title' => $llo->clo?->title,
            'subject_id' => $llo->clo?->subject_id,
            'subject_name' => $llo->clo?->subject?->name,
            'code' => $llo->code,
            'title' => $llo->title,
            'description' => $llo->description,
            'bloom_level' => $llo->bloom_level,
            'lesson_no' => $llo->lesson_no,
            'status' => $this->statusToLabel($llo->status),
            'created_by' => $llo->created_by,
            'creator_name' => $llo->creator?->full_name,
        ];
    }
}
