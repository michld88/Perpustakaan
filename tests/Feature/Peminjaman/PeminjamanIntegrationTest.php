<?php

namespace Tests\Feature\Peminjaman;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\DetailPeminjaman;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Penerbit;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanIntegrationTest extends TestCase
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
        return User::factory()->create([
            'role_id'   => $role->id,
            'is_active' => true,
        ]);
    }

    private function getAnggotaAktif(): Anggota
    {
        $role = Role::where('name', 'anggota')->first();
        $user = User::factory()->create([
            'role_id'   => $role->id,
            'is_active' => true,
        ]);
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

    // ==================== INTEGRATION TESTS ====================

    // Test: siklus peminjaman lengkap dari buat sampai hapus
    public function test_siklus_peminjaman_lengkap(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota    = $this->getAnggotaAktif();
        $buku       = $this->getBukuTersedia();
        $stokAwal   = $buku->jumlah_tersedia;

        // 1. Buat peminjaman
        $response = $this->actingAs($pustakawan)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => [$buku->id],
            'catatan'    => 'Integration test',
        ]);
        $response->assertRedirect(route('peminjaman.index'));

        // 2. Verifikasi stok berkurang
        $buku->refresh();
        $this->assertEquals($stokAwal - 1, $buku->jumlah_tersedia);

        // 3. Verifikasi data peminjaman tersimpan
        $peminjaman = Peminjaman::first();
        $this->assertNotNull($peminjaman);
        $this->assertEquals('dipinjam', $peminjaman->status);
        $this->assertEquals($anggota->id, $peminjaman->anggota_id);

        // 4. Verifikasi jatuh tempo 7 hari
        $expectedJatuhTempo = now()->addDays(7)->toDateString();
        $actualJatuhTempo   = \Carbon\Carbon::parse($peminjaman->getRawOriginal('tanggal_jatuh_tempo'))->toDateString();
        $this->assertEquals($expectedJatuhTempo, $actualJatuhTempo);

        // 5. Verifikasi detail peminjaman
        $this->assertDatabaseHas('detail_peminjaman', [
            'peminjaman_id' => $peminjaman->id,
            'buku_id'       => $buku->id,
        ]);

        // 6. Hapus peminjaman
        $response = $this->actingAs($pustakawan)->delete(route('peminjaman.destroy', $peminjaman->id));
        $response->assertRedirect(route('peminjaman.index'));

        // 7. Verifikasi stok kembali
        $buku->refresh();
        $this->assertEquals($stokAwal, $buku->jumlah_tersedia);

        // 8. Verifikasi peminjaman terhapus
        $this->assertDatabaseMissing('peminjaman', ['id' => $peminjaman->id]);
    }

    // Test: integrasi modul buku dan peminjaman
    public function test_integrasi_buku_dan_peminjaman(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota    = $this->getAnggotaAktif();

        // Buat 3 buku dengan stok berbeda
        $buku1 = $this->getBukuTersedia();
        $buku2 = $this->getBukuTersedia();
        $buku3 = $this->getBukuTersedia();

        // Pinjam 3 buku sekaligus (kuota maksimal)
        $response = $this->actingAs($pustakawan)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => [$buku1->id, $buku2->id, $buku3->id],
        ]);
        $response->assertRedirect(route('peminjaman.index'));

        // Verifikasi semua stok berkurang
        $this->assertEquals(4, $buku1->fresh()->jumlah_tersedia);
        $this->assertEquals(4, $buku2->fresh()->jumlah_tersedia);
        $this->assertEquals(4, $buku3->fresh()->jumlah_tersedia);

        // Verifikasi 3 detail peminjaman
        $peminjaman = Peminjaman::first();
        $this->assertEquals(3, $peminjaman->detail()->count());
    }

    // Test: integrasi modul anggota dan peminjaman
    public function test_integrasi_anggota_dan_peminjaman(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota    = $this->getAnggotaAktif();
        $buku       = $this->getBukuTersedia();

        // Pinjam buku
        $this->actingAs($pustakawan)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => [$buku->id],
        ]);

        // Nonaktifkan anggota
        $anggota->update(['status' => 'nonaktif']);
        $anggota->user->update(['is_active' => false]);

        // Coba pinjam lagi — harus gagal
        $buku2 = $this->getBukuTersedia();
        $response = $this->actingAs($pustakawan)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => [$buku2->id],
        ]);
        $response->assertSessionHasErrors(['anggota_id']);

        // Verifikasi hanya 1 peminjaman yang ada
        $this->assertEquals(1, Peminjaman::where('anggota_id', $anggota->id)->count());
    }

    // Test: buku dengan stok 1 menjadi tidak tersedia setelah dipinjam
    public function test_buku_habis_stok_tidak_bisa_dipinjam_lagi(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota1   = $this->getAnggotaAktif();
        $anggota2   = $this->getAnggotaAktif();

        // Buat buku dengan stok 1
        $kategori = Kategori::firstOrCreate(['nama' => 'Test Kategori']);
        $penerbit = Penerbit::firstOrCreate(['nama' => 'Test Penerbit']);
        $buku = Buku::create([
            'judul'            => 'Buku Stok 1',
            'isbn'             => '978-stok1-' . uniqid(),
            'kategori_id'      => $kategori->id,
            'penerbit_id'      => $penerbit->id,
            'tahun_terbit'     => 2024,
            'jumlah_eksemplar' => 1,
            'jumlah_tersedia'  => 1,
            'status'           => 'tersedia',
        ]);

        // Anggota 1 pinjam buku
        $this->actingAs($pustakawan)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota1->id,
            'buku_ids'   => [$buku->id],
        ]);

        // Verifikasi stok habis
        $buku->refresh();
        $this->assertEquals(0, $buku->jumlah_tersedia);
        $this->assertEquals('dipinjam', $buku->status);

        // Anggota 2 coba pinjam buku yang sama — harus gagal
        $response = $this->actingAs($pustakawan)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota2->id,
            'buku_ids'   => [$buku->id],
        ]);
        $response->assertSessionHasErrors(['buku_ids']);
    }

    // Test: riwayat peminjaman per anggota
    public function test_riwayat_peminjaman_per_anggota(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $anggota1   = $this->getAnggotaAktif();
        $anggota2   = $this->getAnggotaAktif();

        // Anggota 1 pinjam 1 buku
        $buku1 = $this->getBukuTersedia();
        $this->actingAs($pustakawan)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota1->id,
            'buku_ids'   => [$buku1->id],
        ]);

        // Anggota 2 pinjam 1 buku
        $buku2 = $this->getBukuTersedia();
        $this->actingAs($pustakawan)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota2->id,
            'buku_ids'   => [$buku2->id],
        ]);

        // Verifikasi riwayat per anggota
        $response = $this->actingAs($pustakawan)
            ->get(route('peminjaman.riwayat', ['anggota_id' => $anggota1->id]));
        $response->assertStatus(200);

        $this->assertEquals(1, Peminjaman::where('anggota_id', $anggota1->id)->count());
        $this->assertEquals(1, Peminjaman::where('anggota_id', $anggota2->id)->count());
        $this->assertEquals(2, Peminjaman::count());
    }
}