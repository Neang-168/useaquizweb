<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\StudySession;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudySessionController extends Controller
{
    use TranslatesStatus;

    /**
     * Get all study sessions.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $studySessions = StudySession::query()
            ->with('faculty', 'degree', 'major')
            ->when($request->input('faculty_id'), fn ($query, $facultyId) => $query->where('faculty_id', $facultyId))
            ->when($request->input('degree_id'), fn ($query, $degreeId) => $query->where('degree_id', $degreeId))
            ->when($request->input('major_id'), fn ($query, $majorId) => $query->where('major_id', $majorId))
            ->when($request->input('q'), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        $studySessions->getCollection()->transform(fn (StudySession $studySession) => $this->transform($studySession));

        return response()->json($studySessions);
    }

    /**
     * Create a new study session.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $studySession = StudySession::create($data);
        $studySession->load('faculty', 'degree', 'major');

        return response()->json([
            'message' => 'Session created successfully.',
            'session' => $this->transform($studySession),
        ], 201);
    }

    /**
     * Get one study session.
     */
    public function show(StudySession $studySession)
    {
        $studySession->load('faculty', 'degree', 'major');

        return response()->json([
            'session' => $this->transform($studySession),
        ]);
    }

    /**
     * Update study session.
     */
    public function update(Request $request, StudySession $studySession)
    {
        $data = $this->validated($request, $studySession);

        $studySession->update($data);
        $studySession->load('faculty', 'degree', 'major');

        return response()->json([
            'message' => 'Session updated successfully.',
            'session' => $this->transform($studySession),
        ]);
    }

    /**
     * Delete study session.
     */
    public function destroy(StudySession $studySession)
    {
        $studySession->delete();

        return response()->json([
            'message' => 'Session deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?StudySession $studySession = null): array
    {
        $validated = $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'degree_id' => ['nullable', 'exists:degrees,id'],
            'major_id' => ['nullable', 'exists:majors,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('study_sessions', 'code')
                    ->where('faculty_id', $request->input('faculty_id'))
                    ->ignore($studySession?->id),
            ],
            'name_en' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'credits' => ['required', 'integer', 'min:1', 'max:20'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'faculty_id' => $validated['faculty_id'],
            'degree_id' => $validated['degree_id'] ?? null,
            'major_id' => $validated['major_id'] ?? null,
            'code' => $validated['code'],
            'name' => $validated['name_en'],
            'name_kh' => $validated['name_kh'] ?? null,
            'credit' => $validated['credits'],
            'description' => $validated['description'] ?? null,
            'status' => $this->statusToBool($validated['status'] ?? 'Active'),
        ];
    }

    private function transform(StudySession $studySession): array
    {
        return [
            'id' => $studySession->id,
            'faculty_id' => $studySession->faculty_id,
            'faculty_name' => $studySession->faculty?->name,
            'degree_id' => $studySession->degree_id,
            'major_id' => $studySession->major_id,
            'code' => $studySession->code,
            'name_en' => $studySession->name,
            'name_kh' => $studySession->name_kh,
            'credits' => $studySession->credit,
            'description' => $studySession->description,
            'status' => $this->statusToLabel($studySession->status),
        ];
    }
}
