<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create permissions
        $viewDashboardPermission = Permission::firstOrCreate(['name' => 'view dashboard']);
        $manageUsersPermission = Permission::firstOrCreate(['name' => 'manage users']);
        $manageRolesPermission = Permission::firstOrCreate(['name' => 'manage roles']);
        $managePermissionsPermission = Permission::firstOrCreate(['name' => 'manage permissions']);

        // Assign permissions to roles
        $adminRole->givePermissionTo([
            $viewDashboardPermission,
            $manageUsersPermission,
            $manageRolesPermission,
            $managePermissionsPermission
        ]);

        $userRole->givePermissionTo($viewDashboardPermission);

        // Assign roles to users
        $adminUser = \App\Models\User::find(1); // Replace with actual user ID
        $adminUser->assignRole($adminRole);
    }
}

