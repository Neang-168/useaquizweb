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
        ];

        $createdPermissions = [];
        foreach ($permissions as $permissionData) {
            $createdPermissions[$permissionData['name']] = Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                ['module' => $permissionData['module']]
            );
        }

        $roles = [
            'Super Admin' => ['manage_users', 'manage_roles', 'manage_courses', 'manage_exams', 'view_dashboard', 'view_reports', 'manage_profile', 'view_schedule'],
            'Admin' => ['manage_users', 'manage_courses', 'manage_exams', 'view_dashboard', 'view_reports', 'manage_profile', 'view_schedule'],
            'Staff' => ['view_dashboard', 'view_reports', 'manage_profile', 'view_schedule'],
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
