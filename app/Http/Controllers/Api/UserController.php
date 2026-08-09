<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Roles that share the generic "admin profile" (employee code, position,
     * department) instead of a Teacher/Student specific profile table.
     */
    private const ADMIN_LIKE_ROLES = ['Super Admin', 'Admin', 'Staff'];

    /**
     * Get authenticated user.
     */
    public function user(Request $request)
    {
        return response()->json(
            $request->user()
        );
    }

    /**
     * Get authenticated user with role and permissions.
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('role.permissions');

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'name_kh' => $user->name_kh,
            'gender' => $user->gender,
            'dob' => $user->dob?->format('Y-m-d'),
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'avatar_url' => $user->avatar_url,
            'address' => $user->address,
            'status' => $user->status,
            'created_at' => $user->created_at,

            'role' => $user->role?->name,

            'permissions' => $user->role?->permissions
                ->pluck('name')
                ->values()
                ->all() ?? [],
        ]);
    }

    /**
     * Update the authenticated user's own profile info.
     */
    public function updateMe(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'first_name' => [
                'required',
                'string',
                'max:255',
            ],

            'last_name' => [
                'required',
                'string',
                'max:255',
            ],

            'name_kh' => [
                'nullable',
                'string',
                'max:255',
            ],

            'gender' => [
                'nullable',
                'string',
                Rule::in(['Male', 'Female']),
            ],

            'dob' => [
                'nullable',
                'date',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
            ],
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ]);
    }

    /**
     * Upload/replace the authenticated user's own avatar.
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return response()->json([
            'message' => 'Avatar updated successfully.',
            'user' => $user,
        ]);
    }

    /**
     * Change the authenticated user's own password.
     */
    public function changeMyPassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors' => ['current_password' => ['The current password is incorrect.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Get all users. Supports `q` (search) and `roles` (comma-separated or
     * array of role names) to power the tabbed User Management screen.
     */
    public function index(Request $request)
    {
        $perPage = min(
            (int) $request->input('per_page', 15),
            200
        );

        $users = User::query()
            ->with($this->relations())
            ->when($request->input('q'), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('username', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->when($request->input('roles'), function ($query, $roles) {
                $roles = is_array($roles) ? $roles : explode(',', $roles);
                $query->whereHas('role', fn ($query) => $query->whereIn('name', $roles));
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Create a new user (User + role-specific profile row).
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $role = Role::findOrFail($data['role_id']);

        $user = DB::transaction(function () use ($data, $role) {
            $user = User::create($this->userAttributes($data, true));
            $this->syncProfile($user, $role, $data);

            return $user;
        });

        $user->load($this->relations());

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user,
        ], 201);
    }

    /**
     * Get one user.
     */
    public function show(User $user)
    {
        $user->load($this->relations());

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Update user (User + role-specific profile row).
     */
    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);
        $role = Role::findOrFail($data['role_id']);

        DB::transaction(function () use ($data, $user, $role) {
            $user->update($this->userAttributes($data, false));
            $this->syncProfile($user, $role, $data);
        });

        $user->load($this->relations());

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user,
        ]);
    }

    /**
     * Delete user (and any role-specific profile row it owns).
     */
    public function destroy(User $user)
    {
        $user->teacherProfile()->delete();
        $user->studentProfile()->delete();
        $user->adminProfile()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    /**
     * Upload/replace a given user's avatar (admin managing another user,
     * as opposed to uploadAvatar() which is the "me" self-service version).
     */
    public function uploadUserAvatar(Request $request, User $user)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return response()->json([
            'message' => 'Avatar updated successfully.',
            'user' => $user,
        ]);
    }

    /**
     * Relations to eager-load so the frontend has every field it needs
     * (role badge + whichever profile table matches that role) in one call.
     */
    private function relations(): array
    {
        return ['role', 'adminProfile', 'teacherProfile.faculty', 'teacherProfile.degree', 'teacherProfile.major', 'studentProfile'];
    }

    /**
     * Validate the full user form: base `users` columns plus every
     * role-specific profile field, with cross-field requirements resolved
     * from the submitted role_id (e.g. Teacher requires faculty_id).
     */
    private function validated(Request $request, ?User $user = null): array
    {
        $role = Role::find($request->input('role_id'));
        $roleName = $role?->name;

        $employeeCodeRules = array_filter([
            $roleName === 'Teacher' ? 'required' : 'nullable',
            'string',
            'max:50',
            match (true) {
                $roleName === 'Teacher' => Rule::unique('teacher_profiles', 'employee_code')->ignore($user?->teacherProfile?->id),
                in_array($roleName, self::ADMIN_LIKE_ROLES, true) => Rule::unique('admin_profiles', 'employee_code')->ignore($user?->adminProfile?->id),
                default => null,
            },
        ], fn ($rule) => $rule !== null);

        return $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user?->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', Rule::in(['Male', 'Female'])],
            'dob' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['nullable', 'boolean'],

            // Teacher profile
            'employee_code' => $employeeCodeRules,
            'faculty_id' => [Rule::requiredIf($roleName === 'Teacher'), 'nullable', 'exists:faculties,id'],
            'degree_id' => ['nullable', 'exists:degrees,id'],
            'major_id' => ['nullable', 'exists:majors,id'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['nullable', Rule::in(['full_time', 'part_time'])],
            'hire_date' => ['nullable', 'date'],

            // Super Admin / Admin / Staff profile
            'position' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],

            // Student profile
            'student_code' => [
                Rule::requiredIf($roleName === 'Student'),
                'nullable',
                'string',
                'max:50',
                $roleName === 'Student' ? Rule::unique('student_profiles', 'student_code')->ignore($user?->studentProfile?->id) : 'nullable',
            ],
            'admission_date' => ['nullable', 'date'],
        ]);
    }

    /**
     * Pick just the base `users` table columns out of the validated data,
     * hashing the password when one was provided.
     */
    private function userAttributes(array $data, bool $isCreate): array
    {
        $attributes = collect($data)->only([
            'username', 'email', 'first_name', 'last_name', 'name_kh',
            'gender', 'dob', 'phone', 'address', 'role_id', 'status',
        ])->toArray();

        $attributes['status'] = $attributes['status'] ?? true;

        if ($isCreate) {
            $attributes['password'] = Hash::make($data['password']);
        } elseif (!empty($data['password'])) {
            $attributes['password'] = Hash::make($data['password']);
        }

        return $attributes;
    }

    /**
     * Create/update/remove whichever profile row matches the user's role,
     * clearing out any stale profile from a previously-assigned role.
     */
    private function syncProfile(User $user, Role $role, array $data): void
    {
        if ($role->name === 'Teacher') {
            $user->studentProfile()->delete();
            $user->adminProfile()->delete();

            $user->teacherProfile()->updateOrCreate([], [
                'employee_code' => $data['employee_code'],
                'faculty_id' => $data['faculty_id'],
                'degree_id' => $data['degree_id'] ?? null,
                'major_id' => $data['major_id'] ?? null,
                'qualification' => $data['qualification'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'employment_type' => $data['employment_type'] ?? null,
                'hire_date' => $data['hire_date'] ?? null,
            ]);

            return;
        }

        if ($role->name === 'Student') {
            $user->teacherProfile()->delete();
            $user->adminProfile()->delete();

            $user->studentProfile()->updateOrCreate([], [
                'student_code' => $data['student_code'],
                'admission_date' => $data['admission_date'] ?? null,
            ]);

            return;
        }

        $user->teacherProfile()->delete();
        $user->studentProfile()->delete();

        if (in_array($role->name, self::ADMIN_LIKE_ROLES, true) && (
            !empty($data['employee_code']) || !empty($data['position']) || !empty($data['department']) || !empty($data['hire_date'])
        )) {
            $user->adminProfile()->updateOrCreate([], [
                'employee_code' => $data['employee_code'] ?? null,
                'position' => $data['position'] ?? null,
                'department' => $data['department'] ?? null,
                'hire_date' => $data['hire_date'] ?? null,
            ]);
        } else {
            $user->adminProfile()->delete();
        }
    }
}