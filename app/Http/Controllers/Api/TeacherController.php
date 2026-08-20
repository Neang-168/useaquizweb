<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    use TranslatesStatus;

    /**
     * Get all teachers.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $teachers = TeacherProfile::query()
            ->with('user', 'faculty', 'department', 'degree', 'major')
            ->when($request->input('q'), function ($query, $q) {
                $query->where('employee_code', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($query) use ($q) {
                        $query->where('first_name', 'like', "%{$q}%")
                            ->orWhere('last_name', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%");
                    });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $teachers->getCollection()->transform(fn (TeacherProfile $teacher) => $this->transform($teacher));

        return response()->json($teachers);
    }

    /**
     * Create a new teacher (User + TeacherProfile).
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $teacher = DB::transaction(function () use ($validated) {
            $role = Role::where('name', 'Teacher')->first();

            $user = User::create([
                'role_id' => $role?->id,
                'username' => $validated['code'],
                'email' => $validated['email'] ?? "{$validated['code']}@usea.edu.kh",
                'password' => Hash::make($validated['password']),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'name_kh' => $validated['name_kh'],
                'gender' => $validated['gender'],
                'dob' => $validated['dob'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'status' => $validated['user_status'],
            ]);

            return TeacherProfile::create([
                'user_id' => $user->id,
                'employee_code' => $validated['code'],
                'faculty_id' => $validated['faculty_id'],
                'department_id' => $validated['department_id'],
                'degree_id' => $validated['degree_id'],
                'major_id' => $validated['major_id'],
                'qualification' => $validated['qualification'],
                'specialization' => $validated['specialization'],
                'employment_type' => $validated['employment_type'],
                'hire_date' => $validated['hire_date'],
            ]);
        });

        $teacher->load('user', 'faculty', 'department', 'degree', 'major');

        return response()->json([
            'message' => 'Teacher created successfully.',
            'teacher' => $this->transform($teacher),
        ], 201);
    }

    /**
     * Get one teacher.
     */
    public function show(TeacherProfile $teacher)
    {
        $teacher->load('user', 'faculty', 'department', 'degree', 'major');

        return response()->json([
            'teacher' => $this->transform($teacher),
        ]);
    }

    /**
     * Update teacher.
     */
    public function update(Request $request, TeacherProfile $teacher)
    {
        $validated = $this->validated($request, $teacher);

        DB::transaction(function () use ($validated, $teacher) {
            $teacher->user->update([
                'email' => $validated['email'] ?? $teacher->user->email,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'name_kh' => $validated['name_kh'],
                'gender' => $validated['gender'],
                'dob' => $validated['dob'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'status' => $validated['user_status'],
                ...($validated['password'] ? ['password' => Hash::make($validated['password'])] : []),
            ]);

            $teacher->update([
                'employee_code' => $validated['code'],
                'faculty_id' => $validated['faculty_id'],
                'department_id' => $validated['department_id'],
                'degree_id' => $validated['degree_id'],
                'major_id' => $validated['major_id'],
                'qualification' => $validated['qualification'],
                'specialization' => $validated['specialization'],
                'employment_type' => $validated['employment_type'],
                'hire_date' => $validated['hire_date'],
            ]);
        });

        $teacher->load('user', 'faculty', 'department', 'degree', 'major');

        return response()->json([
            'message' => 'Teacher updated successfully.',
            'teacher' => $this->transform($teacher),
        ]);
    }

    /**
     * Delete teacher (soft-deletes the underlying user).
     */
    public function destroy(TeacherProfile $teacher)
    {
        $teacher->user?->delete();
        $teacher->delete();

        return response()->json([
            'message' => 'Teacher deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?TeacherProfile $teacher = null): array
    {
        $userId = $teacher?->user_id;

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('teacher_profiles', 'employee_code')->ignore($teacher?->id),
            ],
            'name_en' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'dob' => ['nullable', 'date'],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'password' => [$teacher ? 'nullable' : 'required', 'string', 'min:8'],
            'faculty_id' => ['required', 'exists:faculties,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'degree_id' => ['nullable', 'exists:degrees,id'],
            'major_id' => ['nullable', 'exists:majors,id'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['Full-Time', 'Part-Time'])],
            'hire_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        [$firstName, $lastName] = $this->splitName($validated['name_en']);

        return [
            'code' => $validated['code'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name_kh' => $validated['name_kh'] ?? null,
            'gender' => $validated['gender'],
            'dob' => $validated['dob'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'password' => $validated['password'] ?? null,
            'faculty_id' => $validated['faculty_id'],
            'department_id' => $validated['department_id'] ?? null,
            'degree_id' => $validated['degree_id'] ?? null,
            'major_id' => $validated['major_id'] ?? null,
            'qualification' => $validated['qualification'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'employment_type' => $validated['type'] === 'Full-Time' ? 'full_time' : 'part_time',
            'hire_date' => $validated['hire_date'] ?? null,
            'user_status' => $this->statusToBool($validated['status']),
        ];
    }

    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        return [$parts[0], $parts[1] ?? ''];
    }

    private function transform(TeacherProfile $teacher): array
    {
        $user = $teacher->user;

        return [
            'id' => $teacher->id,
            'code' => $teacher->employee_code,
            'name_en' => trim($user->first_name . ' ' . $user->last_name),
            'name_kh' => $user->name_kh,
            'gender' => $user->gender,
            'dob' => $user->dob?->toDateString(),
            'phone' => $user->phone,
            'email' => $user->email,
            'address' => $user->address,
            'faculty_id' => $teacher->faculty_id,
            'faculty_name' => $teacher->faculty?->name,
            // Legacy field kept for existing UI compatibility: historically held the
            // faculty's name under a misleading key.
            'department' => $teacher->faculty?->name,
            'department_id' => $teacher->department_id,
            'department_name' => $teacher->department?->name,
            'degree_id' => $teacher->degree_id,
            'degree' => $teacher->degree?->name,
            'major_id' => $teacher->major_id,
            'major_name' => $teacher->major?->name,
            'qualification' => $teacher->qualification,
            'specialization' => $teacher->specialization,
            'type' => $teacher->employment_type === 'full_time' ? 'Full-Time' : 'Part-Time',
            'hire_date' => $teacher->hire_date?->toDateString(),
            'status' => $this->statusToLabel((bool) $user->status),
            'avatar' => $user->avatar,
        ];
    }
}
