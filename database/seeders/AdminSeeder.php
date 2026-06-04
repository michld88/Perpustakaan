<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        User::firstOrCreate(
            ['email' => 'admin@perpustakaan.com'],
            [
                'name' => 'Administrator',
                'email' => 'admin@perpustakaan.com',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $pustakawanRole = Role::where('name', 'pustakawan')->first();
        User::firstOrCreate(
            ['email' => 'pustakawan@perpustakaan.com'],
            [
                'name' => 'Pustakawan Demo',
                'email' => 'pustakawan@perpustakaan.com',
                'password' => Hash::make('password123'),
                'role_id' => $pustakawanRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}