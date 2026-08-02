<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    // Current authenticated user
    Route::get('/user', [UserController::class, 'user']);

    // Current user with role and permissions
    Route::get('/me', [UserController::class, 'me']);

    // Roles
    Route::get('/roles', [RoleController::class, 'index']);

    // Users
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:manage_users');

    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:manage_users');

    Route::get('/users/{user}', [UserController::class, 'show'])
        ->middleware('permission:manage_users');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:manage_users');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:manage_users');
});
