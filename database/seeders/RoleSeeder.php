<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Akses penuh ke seluruh sistem'],
            ['name' => 'pustakawan', 'display_name' => 'Pustakawan', 'description' => 'Mengelola operasional perpustakaan'],
            ['name' => 'anggota', 'display_name' => 'Anggota', 'description' => 'Anggota perpustakaan (mahasiswa/dosen/staf)'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}