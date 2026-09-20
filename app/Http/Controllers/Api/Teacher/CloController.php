<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Api\Teacher\Concerns\ScopesTeacherSubjects;
use App\Http\Controllers\Controller;
use App\Models\Clo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CloController extends Controller
{
    use ScopesTeacherSubjects, TranslatesStatus;

    /**
     * Get this teacher's CLOs, optionally scoped to a subject.
     */
    public function index(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            return response()->json(['data' => []]);
        }

        $ownedSubjectIds = $this->ownedSubjectIds($teacher->id);

        $clos = Clo::query()
            ->whereIn('subject_id', $ownedSubjectIds)
            ->with(['plo', 'subject', 'creator'])
            ->when($request->input('subject_id'), fn ($query, $id) => $query->where('subject_id', $id))
            ->when(! $request->boolean('include_inactive'), fn ($query) => $query->where('status', true))
            ->orderBy('title')
            ->get();

        return response()->json([
            'data' => $clos->map(fn (Clo $clo) => $this->transform($clo)),
        ]);
    }

    /**
     * Create a new CLO.
     */
    public function store(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            abort(403);
        }

        $data = $this->validated($request, $teacher->id);
        $data['created_by'] = $request->user()->id;

        $clo = Clo::create($data);
        $clo->load('plo', 'subject', 'creator');

        return response()->json([
            'message' => 'CLO created successfully.',
            'clo' => $this->transform($clo),
        ], 201);
    }

    /**
     * Get one CLO.
     */
    public function show(Request $request, Clo $clo)
    {
        $this->authorizeOwner($request, $clo);

        $clo->load('plo', 'subject', 'creator');

        return response()->json([
            'clo' => $this->transform($clo),
        ]);
    }

    /**
     * Update CLO.
     */
    public function update(Request $request, Clo $clo)
    {
        $this->authorizeOwner($request, $clo);

        $teacher = $request->user()->teacherProfile;
        $data = $this->validated($request, $teacher->id, $clo);

        $clo->update($data);
        $clo->load('plo', 'subject', 'creator');

        return response()->json([
            'message' => 'CLO updated successfully.',
            'clo' => $this->transform($clo),
        ]);
    }

    /**
     * Delete CLO. Blocked while any LLO references it, since teachers
     * should make a deliberate call before losing that structure (even
     * though the DB-level FK would otherwise cascade-delete them).
     */
    public function destroy(Request $request, Clo $clo)
    {
        $this->authorizeOwner($request, $clo);

        $lloCount = $clo->llos()->count();

        if ($lloCount > 0) {
            throw ValidationException::withMessages([
                'clo' => ["This CLO has {$lloCount} LLO(s) attached. Reassign or delete them first."],
            ]);
        }

        $clo->delete();

        return response()->json(['message' => 'CLO deleted successfully.']);
    }

    private function authorizeOwner(Request $request, Clo $clo): void
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher || ! $this->ownedSubjectIds($teacher->id)->contains($clo->subject_id)) {
            abort(403);
        }
    }

    private function validated(Request $request, int $teacherProfileId, ?Clo $clo = null): array
    {
        $ownedSubjectIds = $this->ownedSubjectIds($teacherProfileId);

        $validated = $request->validate([
            'plo_id' => ['required', 'exists:plos,id'],
            'subject_id' => [
                'required',
                'exists:subjects,id',
                Rule::in($ownedSubjectIds->all()),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('clos', 'code')
                    ->where('subject_id', $request->input('subject_id'))
                    ->ignore($clo?->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'bloom_level' => ['required', Rule::in(['Remember', 'Understand', 'Apply', 'Analyze', 'Evaluate', 'Create'])],
            'status' => ['nullable', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'plo_id' => $validated['plo_id'],
            'subject_id' => $validated['subject_id'],
            'code' => $validated['code'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'bloom_level' => $validated['bloom_level'],
            'status' => $this->statusToBool($validated['status'] ?? 'Active'),
        ];
    }

    private function transform(Clo $clo): array
    {
        return [
            'id' => $clo->id,
            'plo_id' => $clo->plo_id,
            'plo_title' => $clo->plo?->title,
            'subject_id' => $clo->subject_id,
            'subject_name' => $clo->subject?->name,
            'code' => $clo->code,
            'title' => $clo->title,
            'description' => $clo->description,
            'bloom_level' => $clo->bloom_level,
            'status' => $this->statusToLabel($clo->status),
            'created_by' => $clo->created_by,
            'creator_name' => $clo->creator?->full_name,
        ];
    }
}
