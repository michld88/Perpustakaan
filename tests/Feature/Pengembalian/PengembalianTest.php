<?php

namespace Tests\Feature\Pengembalian;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Denda;
use App\Models\DetailPeminjaman;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Penerbit;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengembalianTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\AdminSeeder::class);
    }

    private function getPustakawanUser(): User
    {
        $role = Role::where('name', 'pustakawan')->first();
        return User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
    }

    private function getAnggotaAktif(): Anggota
    {
        $role = Role::where('name', 'anggota')->first();
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        return Anggota::create([
            'user_id'        => $user->id,
            'nim_nip'        => uniqid(),
            'status'         => 'aktif',
            'tanggal_daftar' => now(),
        ]);
    }

    private function getBukuTersedia(): Buku
    {
        $kategori = Kategori::firstOrCreate(['nama' => 'Test Kategori']);
        $penerbit = Penerbit::firstOrCreate(['nama' => 'Test Penerbit']);
        return Buku::create([
            'judul'            => 'Buku Test ' . uniqid(),
            'isbn'             => '978-test-' . uniqid(),
            'kategori_id'      => $kategori->id,
            'penerbit_id'      => $penerbit->id,
            'tahun_terbit'     => 2024,
            'jumlah_eksemplar' => 5,
            'jumlah_tersedia'  => 5,
            'status'           => 'tersedia',
        ]);
    }

    private function buatPeminjaman(User $pustakawan, Anggota $anggota, Buku $buku, array $override = []): Peminjaman
    {
        $peminjaman = Peminjaman::create(array_merge([
            'anggota_id'          => $anggota->id,
            'pustakawan_id'       => $pustakawan->id,
            'tanggal_pinjam'      => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status'              => 'dipinjam',
        ], $override));

        DetailPeminjaman::create([
            'peminjaman_id' => $peminjaman->id,
            'buku_id'       => $buku->id,
            'jumlah'        => 1,
        ]);

        $buku->decrement('jumlah_tersedia');

        // Refresh agar getRawOriginal() terisi dari database
        $peminjaman->refresh();

        return $peminjaman;
    }

    // ==================== PBI-025: PENGEMBALIAN ====================

    public function test_dapat_melihat_halaman_pengembalian(): void
    {
        $user = $this->getPustakawanUser();
        $response = $this->actingAs($user)->get(route('pengembalian.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Pengembalian/Index'));
    }

    public function test_dapat_melihat_detail_pengembalian(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota    = $this->getAnggotaAktif();
        $buku       = $this->getBukuTersedia();
        $peminjaman = $this->buatPeminjaman($pustakawan, $anggota, $buku);

        $response = $this->actingAs($pustakawan)->get(route('pengembalian.show', $peminjaman->id));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Pengembalian/Show'));
    }

    public function test_proses_pengembalian_tepat_waktu(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota    = $this->getAnggotaAktif();
        $buku       = $this->getBukuTersedia();
        $stokAwal   = $buku->jumlah_tersedia;
        $peminjaman = $this->buatPeminjaman($pustakawan, $anggota, $buku);

        $response = $this->actingAs($pustakawan)
            ->post(route('pengembalian.proses', $peminjaman->id));

        $response->assertRedirect(route('pengembalian.index'));
        $response->assertSessionHas('success');

        // Status berubah jadi dikembalikan
        $peminjaman->refresh();
        $this->assertEquals('dikembalikan', $peminjaman->status);
        $this->assertNotNull($peminjaman->getRawOriginal('tanggal_kembali'));

        // Stok kembali
        $buku->refresh();
        $this->assertEquals($stokAwal, $buku->jumlah_tersedia);

        // Tidak ada denda
        $this->assertDatabaseMissing('denda', ['peminjaman_id' => $peminjaman->id]);
    }

    // ==================== PBI-026: DENDA ====================

    public function test_denda_terhitung_otomatis_saat_terlambat(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota    = $this->getAnggotaAktif();
        $buku       = $this->getBukuTersedia();

        $peminjaman = $this->buatPeminjaman($pustakawan, $anggota, $buku, [
            'tanggal_pinjam'      => now()->subDays(10)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
        ]);

        $response = $this->actingAs($pustakawan)
            ->post(route('pengembalian.proses', $peminjaman->id));

        $response->assertRedirect(route('pengembalian.index'));

        // Debug: cek apakah redirect berhasil dulu
        $this->assertDatabaseHas('peminjaman', [
            'id'     => $peminjaman->id,
            'status' => 'dikembalikan',
        ]);

        // Cek denda
        $denda = Denda::where('peminjaman_id', $peminjaman->id)->first();
        $this->assertNotNull($denda, 'Denda seharusnya ada tapi null. Peminjaman ID: ' . $peminjaman->id);
        $this->assertEquals(3, $denda->hari_terlambat);
        $this->assertEquals(3000, $denda->total_denda);
        $this->assertEquals('belum_bayar', $denda->status);
    }

    public function test_tidak_ada_denda_jika_tepat_waktu(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota    = $this->getAnggotaAktif();
        $buku       = $this->getBukuTersedia();
        $peminjaman = $this->buatPeminjaman($pustakawan, $anggota, $buku);

        $this->actingAs($pustakawan)->post(route('pengembalian.proses', $peminjaman->id));

        $this->assertDatabaseMissing('denda', ['peminjaman_id' => $peminjaman->id]);
    }

    public function test_bayar_denda(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota    = $this->getAnggotaAktif();
        $buku       = $this->getBukuTersedia();

        $peminjaman = $this->buatPeminjaman($pustakawan, $anggota, $buku, [
            'tanggal_pinjam'      => now()->subDays(10)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
        ]);

        $this->actingAs($pustakawan)->post(route('pengembalian.proses', $peminjaman->id));

        $denda = Denda::where('peminjaman_id', $peminjaman->id)->first();

        $response = $this->actingAs($pustakawan)
            ->post(route('pengembalian.bayar-denda', $denda->id));

        $response->assertRedirect(route('pengembalian.denda'));
        $denda->refresh();
        $this->assertEquals('sudah_bayar', $denda->status);
        $this->assertNotNull($denda->tanggal_bayar);
    }

    public function test_tidak_bisa_bayar_denda_yang_sudah_dibayar(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota    = $this->getAnggotaAktif();
        $buku       = $this->getBukuTersedia();

        $peminjaman = $this->buatPeminjaman($pustakawan, $anggota, $buku, [
            'tanggal_pinjam'      => now()->subDays(10)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
        ]);

        $this->actingAs($pustakawan)->post(route('pengembalian.proses', $peminjaman->id));

        $denda = Denda::where('peminjaman_id', $peminjaman->id)->first();
        $this->actingAs($pustakawan)->post(route('pengembalian.bayar-denda', $denda->id));

        // Bayar lagi — harus error
        $response = $this->actingAs($pustakawan)
            ->post(route('pengembalian.bayar-denda', $denda->id));
        $response->assertSessionHasErrors(['error']);
    }

    // ==================== PBI-027: PERPANJANGAN ====================

    public function test_dapat_perpanjang_peminjaman(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota    = $this->getAnggotaAktif();
        $buku       = $this->getBukuTersedia();
        $peminjaman = $this->buatPeminjaman($pustakawan, $anggota, $buku);

        $jatuhTempoLama = Carbon::parse($peminjaman->getRawOriginal('tanggal_jatuh_tempo'));

        $response = $this->actingAs($pustakawan)
            ->post(route('pengembalian.perpanjang', $peminjaman->id));

        $response->assertRedirect(route('pengembalian.index'));
        $response->assertSessionHas('success');

        $peminjaman->refresh();
        $this->assertTrue($peminjaman->sudah_diperpanjang);

        // Fix: gunakan tanggal_jatuh_tempo cast bukan getRawOriginal
        $expectedBaru  = $jatuhTempoLama->copy()->addDays(7)->toDateString();
        $actualBaru    = Carbon::parse($peminjaman->getRawOriginal('tanggal_jatuh_tempo'))->toDateString();
        $this->assertEquals($expectedBaru, $actualBaru);
    }

    public function test_tidak_bisa_perpanjang_lebih_dari_sekali(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota    = $this->getAnggotaAktif();
        $buku       = $this->getBukuTersedia();
        $peminjaman = $this->buatPeminjaman($pustakawan, $anggota, $buku);

        // Perpanjang pertama
        $this->actingAs($pustakawan)->post(route('pengembalian.perpanjang', $peminjaman->id));

        // Perpanjang kedua — harus gagal
        $response = $this->actingAs($pustakawan)
            ->post(route('pengembalian.perpanjang', $peminjaman->id));
        $response->assertSessionHasErrors(['error']);
    }

    public function test_tidak_bisa_perpanjang_jika_terlambat(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota    = $this->getAnggotaAktif();
        $buku       = $this->getBukuTersedia();

        $peminjaman = $this->buatPeminjaman($pustakawan, $anggota, $buku, [
            'tanggal_pinjam'      => now()->subDays(10)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
        ]);

        $response = $this->actingAs($pustakawan)
            ->post(route('pengembalian.perpanjang', $peminjaman->id));
        $response->assertSessionHasErrors(['error']);
    }

    public function test_tidak_bisa_proses_pengembalian_yang_sudah_dikembalikan(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota    = $this->getAnggotaAktif();
        $buku       = $this->getBukuTersedia();
        $peminjaman = $this->buatPeminjaman($pustakawan, $anggota, $buku);

        // Kembalikan pertama
        $this->actingAs($pustakawan)->post(route('pengembalian.proses', $peminjaman->id));

        // Kembalikan lagi — harus error
        $response = $this->actingAs($pustakawan)
            ->post(route('pengembalian.proses', $peminjaman->id));
        $response->assertSessionHasErrors(['error']);
    }

    // ==================== INTEGRATION TEST ====================

    public function test_siklus_pengembalian_lengkap_dengan_denda(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota    = $this->getAnggotaAktif();
        $buku       = $this->getBukuTersedia();
        $stokAwal   = $buku->jumlah_tersedia;

        // 1. Buat peminjaman terlambat 5 hari
        $peminjaman = $this->buatPeminjaman($pustakawan, $anggota, $buku, [
            'tanggal_pinjam'      => now()->subDays(12)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(5)->toDateString(),
        ]);

        // 2. Proses pengembalian
        $this->actingAs($pustakawan)->post(route('pengembalian.proses', $peminjaman->id));

        // 3. Verifikasi status
        $peminjaman->refresh();
        $this->assertEquals('dikembalikan', $peminjaman->status);

        // 4. Verifikasi denda
        $denda = Denda::where('peminjaman_id', $peminjaman->id)->first();
        $this->assertNotNull($denda);
        $this->assertEquals(5, $denda->hari_terlambat);
        $this->assertEquals(5000, $denda->total_denda);

        // 5. Bayar denda
        $this->actingAs($pustakawan)->post(route('pengembalian.bayar-denda', $denda->id));
        $denda->refresh();
        $this->assertEquals('sudah_bayar', $denda->status);

        // 6. Stok buku kembali
        $buku->refresh();
        $this->assertEquals($stokAwal, $buku->jumlah_tersedia);
    }

    // ==================== AUTHORIZATION ====================

    public function test_anggota_tidak_bisa_akses_pengembalian(): void
    {
        $role = Role::where('name', 'anggota')->first();
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $response = $this->actingAs($user)->get(route('pengembalian.index'));
        $response->assertStatus(403);
    }

    public function test_guest_tidak_bisa_akses_pengembalian(): void
    {
        $response = $this->get(route('pengembalian.index'));
        $response->assertRedirect(route('login'));
    }
}