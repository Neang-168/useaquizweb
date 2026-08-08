<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Get all permissions, grouped by module.
     */
    public function index()
    {
        return response()->json(
            Permission::query()
                ->select('id', 'name', 'module')
                ->orderBy('module')
                ->orderBy('name')
                ->get()
        );
    }
}
