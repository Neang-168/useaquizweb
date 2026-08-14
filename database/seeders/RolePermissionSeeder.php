<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'manage_users', 'module' => 'users'],
            ['name' => 'manage_roles', 'module' => 'roles'],
            ['name' => 'manage_courses', 'module' => 'courses'],
            ['name' => 'manage_exams', 'module' => 'exams'],
            ['name' => 'take_exam', 'module' => 'exams'],
            ['name' => 'view_dashboard', 'module' => 'dashboard'],
            ['name' => 'view_reports', 'module' => 'reports'],
            ['name' => 'manage_profile', 'module' => 'profile'],
            ['name' => 'view_schedule', 'module' => 'schedule'],
            ['name' => 'manage_academic_structure', 'module' => 'academic_structure'],
            ['name' => 'manage_teachers', 'module' => 'teachers'],
            ['name' => 'manage_students', 'module' => 'students'],
        ];

        $createdPermissions = [];
        foreach ($permissions as $permissionData) {
            $createdPermissions[$permissionData['name']] = Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                ['module' => $permissionData['module']]
            );
        }

        // Only three roles exist: Admin (full administrative access, including
        // role management), Teacher, and Student. There is no separate
        // "Super Admin" or "Staff" role — see the 2026_08_13_000005
        // migration that retired them on already-seeded installs.
        $roles = [
            'Admin' => ['manage_users', 'manage_roles', 'manage_courses', 'manage_exams', 'view_dashboard', 'view_reports', 'manage_profile', 'view_schedule', 'manage_academic_structure', 'manage_teachers', 'manage_students'],
            'Teacher' => ['manage_exams', 'view_dashboard', 'manage_profile', 'view_schedule'],
            'Student' => ['view_dashboard', 'manage_profile', 'view_schedule', 'take_exam'],
        ];

        foreach ($roles as $roleName => $permissionNames) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['description' => $roleName]
            );

            $role->permissions()->sync(
                collect($permissionNames)->map(fn ($name) => $createdPermissions[$name]->id)
            );
        }
    }
}
