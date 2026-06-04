<?php

namespace Tests\Feature\Dashboard;

use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\AdminSeeder::class);
    }

    private function getUser(string $role): User
    {
        $r = Role::where('name', $role)->first();
        return User::factory()->create(['role_id' => $r->id, 'is_active' => true]);
    }

    public function test_admin_dapat_akses_dashboard(): void
    {
        $user = $this->getUser('admin');
        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/AdminDashboard')
            ->has('stats')
            ->has('peminjamanTerbaru')
            ->has('bukuPopuler')
            ->has('grafikBulanan')
        );
    }

    public function test_pustakawan_dapat_akses_dashboard(): void
    {
        $user = $this->getUser('pustakawan');
        $response = $this->actingAs($user)->get(route('pustakawan.dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/PustakawanDashboard')
            ->has('stats')
            ->has('peminjamanTerbaru')
        );
    }

    public function test_anggota_dapat_akses_dashboard(): void
    {
        $user = $this->getUser('anggota');
        Anggota::create([
            'user_id'        => $user->id,
            'status'         => 'aktif',
            'tanggal_daftar' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('anggota.dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/AnggotaDashboard')
            ->has('stats')
            ->has('peminjamanAktif')
            ->has('riwayatTerbaru')
            ->has('reservasiAktif')
        );
    }

    public function test_stats_admin_berisi_data_yang_benar(): void
    {
        $user = $this->getUser('admin');
        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertInertia(fn ($page) => $page
            ->has('stats.total_buku')
            ->has('stats.total_anggota')
            ->has('stats.peminjaman_hari_ini')
            ->has('stats.peminjaman_aktif')
            ->has('stats.buku_terlambat')
            ->has('stats.denda_belum_bayar')
            ->has('stats.reservasi_menunggu')
            ->has('stats.total_peminjaman_bulan')
        );
    }

    public function test_admin_tidak_bisa_akses_dashboard_anggota(): void
    {
        $user = $this->getUser('anggota');
        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_guest_tidak_bisa_akses_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_grafik_bulanan_berisi_6_bulan(): void
    {
        $user = $this->getUser('admin');
        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertInertia(fn ($page) => $page
            ->has('grafikBulanan', 6)
        );
    }
}