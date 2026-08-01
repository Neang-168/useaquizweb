<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/me', function (Request $request) {
        $user = $request->user()->load('role.permissions');

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role?->name,
            'permissions' => $user->role?->permissions->pluck('name')->values()->all() ?? [],
        ]);
    });

    Route::get('/users', function (Request $request) {
        return response()->json([
            'message' => 'You can access this because you have the required permission.',
        ]);
    })->middleware('permission:manage_users');
});

