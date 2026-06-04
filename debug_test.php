<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Simulasi test environment
config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => ':memory:']);

\Illuminate\Support\Facades\Artisan::call('migrate');

// Seed roles
\Database\Seeders\RoleSeeder::class;
$seeder = new \Database\Seeders\RoleSeeder();
$seeder->run();

// Buat user pustakawan
$role = Role::where('name', 'anggota')->first();
echo 'Anggota role id: ' . ($role ? $role->id : 'NULL') . PHP_EOL;

// Buat anggota
$anggotaRole = Role::where('name', 'anggota')->first();
$user = User::create([
    'name'      => 'Test Anggota',
    'email'     => 'anggota.test@email.com',
    'password'  => Hash::make('password123'),
    'role_id'   => $anggotaRole->id,
    'is_active' => true,
]);

$anggota = Anggota::create([
    'user_id'        => $user->id,
    'nim_nip'        => '12345678',
    'tanggal_daftar' => now(),
    'status'         => 'aktif',
]);

// Cek relasi
$anggotaLoaded = Anggota::with(['user'])->first();
echo 'anggota->id: ' . $anggotaLoaded->id . PHP_EOL;
echo 'anggota->user_id: ' . $anggotaLoaded->user_id . PHP_EOL;
echo 'anggota->user->email: ' . ($anggotaLoaded->user ? $anggotaLoaded->user->email : 'NULL') . PHP_EOL;
echo 'anggota->nim_nip: ' . $anggotaLoaded->nim_nip . PHP_EOL;