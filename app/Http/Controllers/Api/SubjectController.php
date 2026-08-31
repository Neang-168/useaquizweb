<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    use TranslatesStatus;

    /**
     * Preview the next auto-generated subject code for a faculty, so the
     * create form can show it before the subject is actually saved.
     */
    public function nextCode(Request $request)
    {
        $validated = $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
        ]);

        $faculty = Faculty::findOrFail($validated['faculty_id']);

        return response()->json(['code' => Subject::generateCode($faculty->code)]);
    }

    /**
     * Get all subjects.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $subjects = Subject::query()
            ->with('faculty', 'department', 'degree', 'major', 'academicYear')
            ->when($request->input('faculty_id'), fn ($query, $facultyId) => $query->where('faculty_id', $facultyId))
            ->when($request->input('department_id'), fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when($request->input('degree_id'), fn ($query, $degreeId) => $query->where('degree_id', $degreeId))
            ->when($request->input('major_id'), fn ($query, $majorId) => $query->where('major_id', $majorId))
            ->when($request->input('academic_year_id'), fn ($query, $id) => $query->where('academic_year_id', $id))
            ->when($request->input('q'), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        $subjects->getCollection()->transform(fn (Subject $subject) => $this->transform($subject));

        return response()->json($subjects);
    }

    /**
     * Create a new subject.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $subject = Subject::create($data);
        $subject->load('faculty', 'department', 'degree', 'major', 'academicYear');

        return response()->json([
            'message' => 'Subject created successfully.',
            'subject' => $this->transform($subject),
        ], 201);
    }

    /**
     * Get one subject.
     */
    public function show(Subject $subject)
    {
        $subject->load('faculty', 'department', 'degree', 'major', 'academicYear');

        return response()->json([
            'subject' => $this->transform($subject),
        ]);
    }

    /**
     * Update subject.
     */
    public function update(Request $request, Subject $subject)
    {
        $data = $this->validated($request, $subject);

        $subject->update($data);
        $subject->load('faculty', 'department', 'degree', 'major', 'academicYear');

        return response()->json([
            'message' => 'Subject updated successfully.',
            'subject' => $this->transform($subject),
        ]);
    }

    /**
     * Delete subject.
     */
    public function destroy(Subject $subject)
    {
        $subject->delete();

        return response()->json([
            'message' => 'Subject deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?Subject $subject = null): array
    {
        $validated = $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'degree_id' => ['nullable', 'exists:degrees,id'],
            'major_id' => ['nullable', 'exists:majors,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')
                    ->where('faculty_id', $request->input('faculty_id'))
                    ->ignore($subject?->id),
            ],
            'name_en' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'credits' => ['required', 'integer', 'min:1', 'max:20'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'faculty_id' => $validated['faculty_id'],
            'department_id' => $validated['department_id'] ?? null,
            'degree_id' => $validated['degree_id'] ?? null,
            'major_id' => $validated['major_id'] ?? null,
            'academic_year_id' => $validated['academic_year_id'] ?? null,
            'code' => $validated['code'],
            'name' => $validated['name_en'],
            'name_kh' => $validated['name_kh'] ?? null,
            'credit' => $validated['credits'],
            'description' => $validated['description'] ?? null,
            'status' => $this->statusToBool($validated['status'] ?? 'Active'),
        ];
    }

    private function transform(Subject $subject): array
    {
        return [
            'id' => $subject->id,
            'faculty_id' => $subject->faculty_id,
            'faculty_name' => $subject->faculty?->name,
            'department_id' => $subject->department_id,
            'department_name' => $subject->department?->name,
            'degree_id' => $subject->degree_id,
            'major_id' => $subject->major_id,
            'major_name' => $subject->major?->name,
            'academic_year_id' => $subject->academic_year_id,
            'academic_year_name' => $subject->academicYear?->name,
            'code' => $subject->code,
            'name_en' => $subject->name,
            'name_kh' => $subject->name_kh,
            'credits' => $subject->credit,
            'description' => $subject->description,
            'status' => $this->statusToLabel($subject->status),
        ];
    }
}
