<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->syncPermissions(Permission::all());


        // Admin
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view dashboard',
            'manage users',
        ]);

        // Guest/User
        $user = Role::firstOrCreate(['name' => 'user']);
        $user->syncPermissions(['view dashboard']);
    }
}
