<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        
        $permissions = [
            'view-dashboard',
            'manage users', 'view-users', 'create-users', 'edit-users', 'delete-users',
            'manage roles', 'view-roles', 'create-roles', 'edit-roles', 'delete-roles',
            'manage permissions', 'view-permissions', 'create-permissions', 'edit-permissions', 'delete-permissions',
            'manage items', 'view-items', 'create-items', 'edit-items', 'delete-items',
            'manage borrowing', 'view-borrowing', 'create-borrowing', 'edit-borrowing', 'delete-borrowing'
        ];

        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

       
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'api']);
        $adminRole      = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $userRole       = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'api']);

       
        $superAdminRole->givePermissionTo(Permission::all());

       
        $adminRole->givePermissionTo([
            'view-dashboard',
            'manage users', 'view-users', 'create-users', 'edit-users', 'delete-users',
            'manage items', 'view-items', 'create-items', 'edit-items', 'delete-items',
            'manage borrowing', 'view-borrowing', 'create-borrowing', 'edit-borrowing'
        ]);


        $userRole->givePermissionTo([
            'view-dashboard',
            'view-items',
            'view-borrowing',
            'create-borrowing'
        ]);
    }
}
