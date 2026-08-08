<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Get all roles.
     */
    public function index()
    {
        return response()->json(
            Role::query()
                ->select('id', 'name', 'description')
                ->withCount('users')
                ->orderBy('name')
                ->get()
        );
    }

    /**
     * Get one role with its permissions.
     */
    public function show(Role $role)
    {
        $role->load('permissions');

        return response()->json([
            'role' => $role,
        ]);
    }

    /**
     * Update which permissions are assigned to this role.
     * Roles themselves (the 5 fixed roles) are not created or deleted here.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->permissions()->sync($validated['permission_ids']);
        $role->load('permissions');

        return response()->json([
            'message' => 'Permissions updated successfully.',
            'role' => $role,
        ]);
    }
}
