<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view dashboard',
            'manage items',
            'manage borrowing',
            'manage users',

            // Izin spesifik untuk intents
            'view-items',
            'create-items',
            'edit-edit-items',
            'delete-items',
            // Izin spesifik untuk questions
            'view-borrowing',
            'create-borrowing',
            'edit-borrowing',
            'delete-borrowing',
            // Izin spesifik untuk responses
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}