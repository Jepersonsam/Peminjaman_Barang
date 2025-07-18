<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Buat user Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'), // Ganti dengan password aman
                'code' => 'SA001' // Jika ada field code
            ]
        );

        // Pastikan role sudah ada
        $role = Role::where('name', 'super admin')->where('guard_name', 'api')->first();

        if ($role) {
            // Assign role super admin
            $superAdmin->assignRole($role);
        }
    }
}
