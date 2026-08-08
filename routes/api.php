<?php

use App\Http\Controllers\Api\AcademicYearController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClassroomController;
use App\Http\Controllers\Api\DegreeController;
use App\Http\Controllers\Api\FacultyController;
use App\Http\Controllers\Api\MajorController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\StageController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TeacherController;
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

    Route::get('/roles/{role}', [RoleController::class, 'show'])
        ->middleware('permission:manage_roles');

    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:manage_roles');

    // Permissions
    Route::get('/permissions', [PermissionController::class, 'index'])
        ->middleware('permission:manage_roles');

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

    // Academic structure: faculties, degrees, majors, subjects, academic years, shifts, stages, classes
    Route::middleware('permission:manage_academic_structure')->group(function () {
        Route::apiResource('faculties', FacultyController::class)->parameters(['faculties' => 'faculty']);
        Route::apiResource('degrees', DegreeController::class)->parameters(['degrees' => 'degree']);
        Route::apiResource('majors', MajorController::class)->parameters(['majors' => 'major']);
        Route::apiResource('subjects', SubjectController::class)->parameters(['subjects' => 'subject']);

        Route::post('/academic-years/{academicYear}/set-current', [AcademicYearController::class, 'setCurrent']);
        Route::apiResource('academic-years', AcademicYearController::class)->parameters(['academic-years' => 'academicYear']);

        Route::apiResource('shifts', ShiftController::class)->parameters(['shifts' => 'shift']);
        Route::apiResource('stages', StageController::class)->parameters(['stages' => 'stage']);
        Route::apiResource('classes', ClassroomController::class)->parameters(['classes' => 'class']);
    });

    // Teachers
    Route::apiResource('teachers', TeacherController::class)
        ->parameters(['teachers' => 'teacher'])
        ->middleware('permission:manage_teachers');

    // Students
    Route::apiResource('students', StudentController::class)
        ->parameters(['students' => 'student'])
        ->middleware('permission:manage_students');
});
