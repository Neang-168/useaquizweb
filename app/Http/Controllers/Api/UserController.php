<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
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
            'status' => $user->status,

            'role' => $user->role?->name,

            'permissions' => $user->role?->permissions
                ->pluck('name')
                ->values()
                ->all() ?? [],
        ]);
    }

    /**
     * Get all users.
     */
    public function index(Request $request)
    {
        $perPage = min(
            (int) $request->input('per_page', 15),
            100
        );

        $users = User::query()
            ->with('role')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Create a new user.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username'),
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'password' => [
                'required',
                'string',
                'min:8',
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

            'role_id' => [
                'nullable',
                'exists:roles,id',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['status'] = $data['status'] ?? true;

        $user = User::create($data);

        $user->load('role');

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
        $user->load('role');

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')
                    ->ignore($user->id),
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
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

            'role_id' => [
                'nullable',
                'exists:roles,id',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        $user->load('role');

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user,
        ]);
    }

    /**
     * Delete user.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}