<?php

namespace Tests\Feature\Reservasi;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\DetailPeminjaman;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Penerbit;
use App\Models\Reservasi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReservasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\AdminSeeder::class);
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

    private function getPustakawanUser(): User
    {
        $role = Role::where('name', 'pustakawan')->first();
        return User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
    }

    private function getBukuDipinjam(): Buku
    {
        $kategori = Kategori::firstOrCreate(['nama' => 'Test']);
        $penerbit = Penerbit::firstOrCreate(['nama' => 'Test']);
        return Buku::create([
            'judul'            => 'Buku Dipinjam ' . uniqid(),
            'isbn'             => '978-' . uniqid(),
            'kategori_id'      => $kategori->id,
            'penerbit_id'      => $penerbit->id,
            'tahun_terbit'     => 2024,
            'jumlah_eksemplar' => 1,
            'jumlah_tersedia'  => 0,
            'status'           => 'dipinjam',
        ]);
    }

    // ==================== PBI-031: FORM RESERVASI ====================

    public function test_anggota_dapat_melihat_halaman_reservasi(): void
    {
        [$user] = $this->getAnggotaUser();
        $response = $this->actingAs($user)->get(route('reservasi.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Reservasi/Index'));
    }

    public function test_anggota_dapat_melihat_form_reservasi(): void
    {
        [$user] = $this->getAnggotaUser();
        $response = $this->actingAs($user)->get(route('reservasi.create'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Reservasi/Create'));
    }

    public function test_anggota_dapat_membuat_reservasi(): void
    {
        [$user, $anggota] = $this->getAnggotaUser();
        $buku = $this->getBukuDipinjam();

        $response = $this->actingAs($user)->post(route('reservasi.store'), [
            'buku_id' => $buku->id,
        ]);

        $response->assertRedirect(route('reservasi.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('reservasi', [
            'anggota_id' => $anggota->id,
            'buku_id'    => $buku->id,
            'status'     => 'menunggu',
        ]);
    }

    public function test_tidak_bisa_reservasi_buku_tersedia(): void
    {
        [$user] = $this->getAnggotaUser();
        $kategori = Kategori::firstOrCreate(['nama' => 'Test']);
        $penerbit = Penerbit::firstOrCreate(['nama' => 'Test']);
        $buku = Buku::create([
            'judul'            => 'Buku Tersedia',
            'isbn'             => '978-' . uniqid(),
            'kategori_id'      => $kategori->id,
            'penerbit_id'      => $penerbit->id,
            'tahun_terbit'     => 2024,
            'jumlah_eksemplar' => 1,
            'jumlah_tersedia'  => 1,
            'status'           => 'tersedia',
        ]);

        $response = $this->actingAs($user)->post(route('reservasi.store'), [
            'buku_id' => $buku->id,
        ]);

        $response->assertSessionHasErrors(['buku_id']);
    }

    public function test_tidak_bisa_reservasi_buku_yang_sama_dua_kali(): void
    {
        [$user, $anggota] = $this->getAnggotaUser();
        $buku = $this->getBukuDipinjam();

        $this->actingAs($user)->post(route('reservasi.store'), ['buku_id' => $buku->id]);
        $response = $this->actingAs($user)->post(route('reservasi.store'), ['buku_id' => $buku->id]);

        $response->assertSessionHasErrors(['buku_id']);
    }

    public function test_anggota_dapat_batalkan_reservasi(): void
    {
        [$user, $anggota] = $this->getAnggotaUser();
        $buku = $this->getBukuDipinjam();

        $this->actingAs($user)->post(route('reservasi.store'), ['buku_id' => $buku->id]);
        $reservasi = Reservasi::where('anggota_id', $anggota->id)->first();

        $response = $this->actingAs($user)->delete(route('reservasi.destroy', $reservasi->id));

        $response->assertRedirect(route('reservasi.index'));
        $this->assertDatabaseHas('reservasi', [
            'id'     => $reservasi->id,
            'status' => 'dibatalkan',
        ]);
    }

    // ==================== PBI-032: NOTIFIKASI ====================

    public function test_notifikasi_terkirim_saat_buku_tersedia(): void
    {
        Notification::fake();

        [$user, $anggota] = $this->getAnggotaUser();
        $buku = $this->getBukuDipinjam();

        Reservasi::create([
            'anggota_id'        => $anggota->id,
            'buku_id'           => $buku->id,
            'status'            => 'menunggu',
            'tanggal_reservasi' => now()->toDateString(),
        ]);

        \App\Http\Controllers\ReservasiController::notifikasiReservasiTersedia($buku->id);

        Notification::assertSentTo(
            $user,
            \App\Notifications\ReservasiBukuTersedia::class
        );
    }

    public function test_status_reservasi_berubah_saat_buku_tersedia(): void
    {
        [$user, $anggota] = $this->getAnggotaUser();
        $buku = $this->getBukuDipinjam();

        Reservasi::create([
            'anggota_id'        => $anggota->id,
            'buku_id'           => $buku->id,
            'status'            => 'menunggu',
            'tanggal_reservasi' => now()->toDateString(),
        ]);

        Notification::fake();
        \App\Http\Controllers\ReservasiController::notifikasiReservasiTersedia($buku->id);

        $this->assertDatabaseHas('reservasi', [
            'anggota_id' => $anggota->id,
            'buku_id'    => $buku->id,
            'status'     => 'tersedia',
        ]);
    }

    public function test_anggota_dapat_ambil_reservasi(): void
    {
        [$user, $anggota] = $this->getAnggotaUser();
        $buku = $this->getBukuDipinjam();

        $reservasi = Reservasi::create([
            'anggota_id'        => $anggota->id,
            'buku_id'           => $buku->id,
            'status'            => 'tersedia',
            'tanggal_reservasi' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('reservasi.ambil', $reservasi->id));

        $response->assertRedirect(route('reservasi.index'));
        $this->assertDatabaseHas('reservasi', [
            'id'     => $reservasi->id,
            'status' => 'diambil',
        ]);
    }

    // ==================== AUTHORIZATION ====================

    public function test_guest_tidak_bisa_akses_reservasi(): void
    {
        $response = $this->get(route('reservasi.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_pustakawan_dapat_lihat_semua_reservasi(): void
    {
        $pustakawan = $this->getPustakawanUser();
        $response = $this->actingAs($pustakawan)->get(route('reservasi.index'));
        $response->assertStatus(200);
    }
}