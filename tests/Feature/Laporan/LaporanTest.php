<?php

namespace Tests\Feature\Laporan;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Denda;
use App\Models\DetailPeminjaman;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Penerbit;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\AdminSeeder::class);
    }

    private function getAdminUser(): User
    {
        $role = Role::where('name', 'admin')->first();
        return User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
    }

    private function getPustakawanUser(): User
    {
        $role = Role::where('name', 'pustakawan')->first();
        return User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
    }

    private function getAnggotaUser(): array
    {
        $role = Role::where('name', 'anggota')->first();
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $anggota = Anggota::create([
            'user_id'        => $user->id,
            'nim_nip'        => uniqid(),
            'status'         => 'aktif',
            'tanggal_daftar' => now(),
        ]);
        return [$user, $anggota];
    }

    private function buatBuku(): Buku
    {
        $kategori = Kategori::firstOrCreate(['nama' => 'Test']);
        $penerbit = Penerbit::firstOrCreate(['nama' => 'Test']);
        return Buku::create([
            'judul'            => 'Buku Test ' . uniqid(),
            'isbn'             => '978-' . uniqid(),
            'kategori_id'      => $kategori->id,
            'penerbit_id'      => $penerbit->id,
            'tahun_terbit'     => 2024,
            'jumlah_eksemplar' => 5,
            'jumlah_tersedia'  => 5,
            'status'           => 'tersedia',
        ]);
    }

    private function buatPeminjaman(User $pustakawan, Anggota $anggota, Buku $buku): Peminjaman
    {
        $peminjaman = Peminjaman::create([
            'anggota_id'          => $anggota->id,
            'pustakawan_id'       => $pustakawan->id,
            'tanggal_pinjam'      => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status'              => 'dipinjam',
        ]);
        DetailPeminjaman::create([
            'peminjaman_id' => $peminjaman->id,
            'buku_id'       => $buku->id,
            'jumlah'        => 1,
        ]);
        return $peminjaman->refresh();
    }

    // ==================== PBI-034: STATISTIK PEMINJAMAN ====================

    public function test_dapat_akses_halaman_laporan(): void
    {
        $user = $this->getAdminUser();
        $response = $this->actingAs($user)->get(route('laporan.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Laporan/Index'));
    }

    public function test_dapat_lihat_statistik_peminjaman_bulanan(): void
    {
        $user = $this->getAdminUser();
        $response = $this->actingAs($user)->get(route('laporan.statistik-peminjaman', ['periode' => 'bulanan']));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Laporan/StatistikPeminjaman')
            ->has('data')
            ->has('summary')
        );
    }

    public function test_dapat_lihat_statistik_peminjaman_harian(): void
    {
        $user = $this->getAdminUser();
        $response = $this->actingAs($user)->get(route('laporan.statistik-peminjaman', ['periode' => 'harian']));
        $response->assertStatus(200);
    }

    public function test_dapat_lihat_statistik_peminjaman_tahunan(): void
    {
        $user = $this->getAdminUser();
        $response = $this->actingAs($user)->get(route('laporan.statistik-peminjaman', ['periode' => 'tahunan']));
        $response->assertStatus(200);
    }

    public function test_summary_statistik_akurat(): void
    {
        $pustakawan = $this->getPustakawanUser();
        [$userAnggota, $anggota] = $this->getAnggotaUser();
        $buku = $this->buatBuku();

        $this->buatPeminjaman($pustakawan, $anggota, $buku);

        $user = $this->getAdminUser();
        $response = $this->actingAs($user)->get(route('laporan.statistik-peminjaman', [
            'tahun' => now()->year,
        ]));

        $response->assertInertia(fn ($page) => $page
            ->where('summary.total_peminjaman', 1)
            ->where('summary.total_aktif', 1)
        );
    }

    // ==================== PBI-035: BUKU TERPOPULER ====================

    public function test_dapat_lihat_buku_terpopuler(): void
    {
        $user = $this->getAdminUser();
        $response = $this->actingAs($user)->get(route('laporan.buku-terpopuler'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Laporan/BukuTerpopuler')
            ->has('buku')
        );
    }

    public function test_buku_terpopuler_terurut_benar(): void
    {
        $pustakawan = $this->getPustakawanUser();
        [$userAnggota1, $anggota1] = $this->getAnggotaUser();
        [$userAnggota2, $anggota2] = $this->getAnggotaUser();

        $buku1 = $this->buatBuku();
        $buku2 = $this->buatBuku();

        // Buku1 dipinjam 2x, buku2 dipinjam 1x
        $this->buatPeminjaman($pustakawan, $anggota1, $buku1);
        $this->buatPeminjaman($pustakawan, $anggota2, $buku1);
        $this->buatPeminjaman($pustakawan, $anggota1, $buku2);

        $user = $this->getAdminUser();
        $response = $this->actingAs($user)->get(route('laporan.buku-terpopuler', ['tahun' => now()->year]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('buku.0.id', $buku1->id)
        );
    }

    // ==================== PBI-036: ANGGOTA TERAKTIF ====================

    public function test_dapat_lihat_anggota_teraktif(): void
    {
        $user = $this->getAdminUser();
        $response = $this->actingAs($user)->get(route('laporan.anggota-teraktif'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Laporan/AnggotaTeraktif')
            ->has('anggota')
        );
    }

    // ==================== DENDA TERKUMPUL ====================

    public function test_dapat_lihat_denda_terkumpul(): void
    {
        $user = $this->getAdminUser();
        $response = $this->actingAs($user)->get(route('laporan.denda-terkumpul'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Laporan/DendaTerkumpul')
            ->has('totalBelumBayar')
            ->has('totalSudahBayar')
        );
    }

    public function test_total_denda_akurat(): void
    {
        $pustakawan = $this->getPustakawanUser();
        [$userAnggota, $anggota] = $this->getAnggotaUser();
        $buku = $this->buatBuku();

        $peminjaman = Peminjaman::create([
            'anggota_id'          => $anggota->id,
            'pustakawan_id'       => $pustakawan->id,
            'tanggal_pinjam'      => now()->subDays(10)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
            'status'              => 'dikembalikan',
            'tanggal_kembali'     => now()->toDateString(),
        ]);

        Denda::create([
            'peminjaman_id'  => $peminjaman->id,
            'hari_terlambat' => 3,
            'tarif_per_hari' => 1000,
            'total_denda'    => 3000,
            'status'         => 'belum_bayar',
        ]);

        $user = $this->getAdminUser();
        $response = $this->actingAs($user)->get(route('laporan.denda-terkumpul', ['tahun' => now()->year]));

        $response->assertInertia(fn ($page) => $page
            ->where('totalBelumBayar', 3000)
            ->where('totalSudahBayar', 0)
        );
    }

    // ==================== PBI-037 & 038: EKSPOR ====================

    public function test_dapat_ekspor_excel_peminjaman(): void
    {
        $user = $this->getAdminUser();
        $response = $this->actingAs($user)->get(route('laporan.ekspor-excel', [
            'jenis' => 'peminjaman',
            'tahun' => now()->year,
        ]));
        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml', $response->headers->get('content-type'));
    }

    public function test_dapat_ekspor_excel_buku_terpopuler(): void
    {
        $user = $this->getAdminUser();
        $response = $this->actingAs($user)->get(route('laporan.ekspor-excel', [
            'jenis' => 'buku_terpopuler',
            'tahun' => now()->year,
        ]));
        $response->assertStatus(200);
    }

    public function test_dapat_ekspor_pdf(): void
    {
        $user = $this->getAdminUser();
        $response = $this->actingAs($user)->get(route('laporan.ekspor-pdf', [
            'jenis' => 'peminjaman',
            'tahun' => now()->year,
        ]));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    // ==================== AUTHORIZATION ====================

    public function test_anggota_tidak_bisa_akses_laporan(): void
    {
        [$user] = $this->getAnggotaUser();
        $response = $this->actingAs($user)->get(route('laporan.index'));
        $response->assertStatus(403);
    }

    public function test_guest_tidak_bisa_akses_laporan(): void
    {
        $response = $this->get(route('laporan.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_pustakawan_dapat_akses_laporan(): void
    {
        $user = $this->getPustakawanUser();
        $response = $this->actingAs($user)->get(route('laporan.index'));
        $response->assertStatus(200);
    }
}