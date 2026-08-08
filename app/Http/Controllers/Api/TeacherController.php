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
use Illuminate\Support\Str;
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
            ->with('user', 'faculty', 'degree')
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
                'password' => Hash::make(Str::random(16)),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'name_kh' => $validated['name_kh'],
                'gender' => $validated['gender'],
                'phone' => $validated['phone'],
                'status' => $validated['user_status'],
            ]);

            return TeacherProfile::create([
                'user_id' => $user->id,
                'employee_code' => $validated['code'],
                'faculty_id' => $validated['faculty_id'],
                'degree_id' => $validated['degree_id'],
                'employment_type' => $validated['employment_type'],
            ]);
        });

        $teacher->load('user', 'faculty', 'degree');

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
        $teacher->load('user', 'faculty', 'degree');

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
                'phone' => $validated['phone'],
                'status' => $validated['user_status'],
            ]);

            $teacher->update([
                'employee_code' => $validated['code'],
                'faculty_id' => $validated['faculty_id'],
                'degree_id' => $validated['degree_id'],
                'employment_type' => $validated['employment_type'],
            ]);
        });

        $teacher->load('user', 'faculty', 'degree');

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
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['required', 'string', 'max:30'],
            'faculty_id' => ['required', 'exists:faculties,id'],
            'degree_id' => ['nullable', 'exists:degrees,id'],
            'type' => ['required', Rule::in(['Full-Time', 'Part-Time'])],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        [$firstName, $lastName] = $this->splitName($validated['name_en']);

        return [
            'code' => $validated['code'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name_kh' => $validated['name_kh'] ?? null,
            'gender' => $validated['gender'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'faculty_id' => $validated['faculty_id'],
            'degree_id' => $validated['degree_id'] ?? null,
            'employment_type' => $validated['type'] === 'Full-Time' ? 'full_time' : 'part_time',
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
            'phone' => $user->phone,
            'email' => $user->email,
            'faculty_id' => $teacher->faculty_id,
            'department' => $teacher->faculty?->name,
            'degree_id' => $teacher->degree_id,
            'degree' => $teacher->degree?->name,
            'type' => $teacher->employment_type === 'full_time' ? 'Full-Time' : 'Part-Time',
            'status' => $this->statusToLabel((bool) $user->status),
            'avatar' => $user->avatar,
        ];
    }
}
