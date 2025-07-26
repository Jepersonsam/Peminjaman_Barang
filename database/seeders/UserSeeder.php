<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
   
    public function run(): void
    {
        // Pastikan role superadmin sudah ada
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'api']);

        // Buat user superadmin
        $user = User::firstOrCreate(
            ['email' => 'superadmin@example.com'], 
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password123'),
            ]
        );

      
        $user->assignRole($superAdminRole);

        echo "User Super Admin berhasil dibuat dengan email: superadmin@example.com dan password: password123\n";
    }
}
