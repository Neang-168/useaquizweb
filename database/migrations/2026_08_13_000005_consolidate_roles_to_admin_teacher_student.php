<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Retire the "Super Admin" and "Staff" roles so the system only has
     * three roles: Admin, Teacher, Student. Any user on a retired role is
     * reassigned to Admin first, and Admin absorbs every permission Super
     * Admin had (e.g. manage_roles), so no access is lost in the merge.
     */
    public function up(): void
    {
        $admin = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Admin']);
        $superAdmin = Role::where('name', 'Super Admin')->first();
        $staff = Role::where('name', 'Staff')->first();

        if ($superAdmin) {
            $permissionIds = $admin->permissions()->pluck('permissions.id')
                ->merge($superAdmin->permissions()->pluck('permissions.id'))
                ->unique()
                ->values();
            $admin->permissions()->sync($permissionIds);

            User::where('role_id', $superAdmin->id)->update(['role_id' => $admin->id]);
        }

        if ($staff) {
            User::where('role_id', $staff->id)->update(['role_id' => $admin->id]);
        }

        // role_permissions rows for these roles cascade-delete automatically.
        Role::whereIn('name', ['Super Admin', 'Staff'])->delete();
    }

    /**
     * Best-effort reverse: recreates the retired roles. Historical
     * user/permission assignments cannot be restored automatically.
     */
    public function down(): void
    {
        Role::firstOrCreate(['name' => 'Super Admin'], ['description' => 'Super Admin']);
        Role::firstOrCreate(['name' => 'Staff'], ['description' => 'Staff']);
    }
};
