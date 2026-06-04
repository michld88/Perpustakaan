<?php

namespace Tests\Feature\Peminjaman;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\DetailPeminjaman;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Penerbit;
use App\Models\Penulis;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PeminjamanTest extends TestCase
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
            'nim_nip'        => '12345678',
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

    // ==================== PBI-019: FORM PEMINJAMAN ====================

    public function test_pustakawan_can_view_peminjaman_index(): void
    {
        $user = $this->getPustakawanUser();
        $response = $this->actingAs($user)->get(route('peminjaman.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Peminjaman/Index'));
    }

    public function test_pustakawan_can_view_create_form(): void
    {
        $user = $this->getPustakawanUser();
        $response = $this->actingAs($user)->get(route('peminjaman.create'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Peminjaman/Create'));
    }

    public function test_pustakawan_can_create_peminjaman(): void
    {
        $user    = $this->getPustakawanUser();
        $anggota = $this->getAnggotaAktif();
        $buku    = $this->getBukuTersedia();

        $response = $this->actingAs($user)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => [$buku->id],
            'catatan'    => 'Test peminjaman',
        ]);

        $response->assertRedirect(route('peminjaman.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('peminjaman', [
            'anggota_id' => $anggota->id,
            'status'     => 'dipinjam',
        ]);
        $this->assertDatabaseHas('detail_peminjaman', [
            'buku_id' => $buku->id,
        ]);
    }

    public function test_stok_buku_berkurang_setelah_dipinjam(): void
    {
        $user    = $this->getPustakawanUser();
        $anggota = $this->getAnggotaAktif();
        $buku    = $this->getBukuTersedia();
        $stokAwal = $buku->jumlah_tersedia;

        $this->actingAs($user)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => [$buku->id],
        ]);

        $buku->refresh();
        $this->assertEquals($stokAwal - 1, $buku->jumlah_tersedia);
    }

    // ==================== PBI-020: JATUH TEMPO ====================

    public function test_tanggal_jatuh_tempo_otomatis_7_hari(): void
    {
        $user    = $this->getPustakawanUser();
        $anggota = $this->getAnggotaAktif();
        $buku    = $this->getBukuTersedia();

        $this->actingAs($user)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => [$buku->id],
        ]);

        $peminjaman = Peminjaman::first();
        $expectedJatuhTempo = now()->addDays(7)->toDateString();
        $this->assertEquals($expectedJatuhTempo, \Carbon\Carbon::parse($peminjaman->getRawOriginal('tanggal_jatuh_tempo'))->toDateString());
    }

    // ==================== VALIDASI KUOTA ====================

    public function test_tidak_bisa_pinjam_melebihi_kuota(): void
    {
        $user    = $this->getPustakawanUser();
        $anggota = $this->getAnggotaAktif();

        // Buat 4 buku (melebihi kuota 3)
        $bukuIds = [];
        for ($i = 0; $i < 4; $i++) {
            $bukuIds[] = $this->getBukuTersedia()->id;
        }

        $response = $this->actingAs($user)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => $bukuIds,
        ]);

        $response->assertSessionHasErrors(['buku_ids']);
        $this->assertDatabaseMissing('peminjaman', ['anggota_id' => $anggota->id]);
    }

    public function test_tidak_bisa_pinjam_jika_kuota_sudah_penuh(): void
    {
        $user    = $this->getPustakawanUser();
        $anggota = $this->getAnggotaAktif();

        // Pinjam 3 buku dulu (kuota penuh)
        $bukuIds = [];
        for ($i = 0; $i < 3; $i++) {
            $bukuIds[] = $this->getBukuTersedia()->id;
        }

        $this->actingAs($user)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => $bukuIds,
        ]);

        // Coba pinjam 1 buku lagi
        $bukuBaru = $this->getBukuTersedia();
        $response = $this->actingAs($user)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => [$bukuBaru->id],
        ]);

        $response->assertSessionHasErrors(['buku_ids']);
    }

    public function test_tidak_bisa_pinjam_jika_anggota_nonaktif(): void
    {
        $user    = $this->getPustakawanUser();
        $anggota = $this->getAnggotaAktif();
        $buku    = $this->getBukuTersedia();

        // Nonaktifkan anggota
        $anggota->update(['status' => 'nonaktif']);

        $response = $this->actingAs($user)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => [$buku->id],
        ]);

        $response->assertSessionHasErrors(['anggota_id']);
    }

    public function test_tidak_bisa_pinjam_buku_tidak_tersedia(): void
    {
        $user    = $this->getPustakawanUser();
        $anggota = $this->getAnggotaAktif();
        $buku    = $this->getBukuTersedia();

        // Habiskan stok buku
        $buku->update(['jumlah_tersedia' => 0, 'status' => 'dipinjam']);

        $response = $this->actingAs($user)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => [$buku->id],
        ]);

        $response->assertSessionHasErrors(['buku_ids']);
    }

    // ==================== PBI-021: DAFTAR & RIWAYAT ====================

    public function test_dapat_melihat_detail_peminjaman(): void
    {
        $user    = $this->getPustakawanUser();
        $anggota = $this->getAnggotaAktif();
        $buku    = $this->getBukuTersedia();

        $this->actingAs($user)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => [$buku->id],
        ]);

        $peminjaman = Peminjaman::first();
        $response = $this->actingAs($user)->get(route('peminjaman.show', $peminjaman->id));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Peminjaman/Show'));
    }

    public function test_dapat_melihat_riwayat_peminjaman(): void
    {
        $user = $this->getPustakawanUser();
        $response = $this->actingAs($user)->get(route('peminjaman.riwayat'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Peminjaman/Riwayat'));
    }

    public function test_dapat_hapus_peminjaman_aktif(): void
    {
        $user    = $this->getPustakawanUser();
        $anggota = $this->getAnggotaAktif();
        $buku    = $this->getBukuTersedia();

        $this->actingAs($user)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => [$buku->id],
        ]);

        $peminjaman = Peminjaman::first();
        $response = $this->actingAs($user)->delete(route('peminjaman.destroy', $peminjaman->id));

        $response->assertRedirect(route('peminjaman.index'));
        $this->assertDatabaseMissing('peminjaman', ['id' => $peminjaman->id]);

        // Stok buku harus kembali
        $buku->refresh();
        $this->assertEquals(5, $buku->jumlah_tersedia);
    }

    public function test_stok_buku_kembali_setelah_peminjaman_dihapus(): void
    {
        $user    = $this->getPustakawanUser();
        $anggota = $this->getAnggotaAktif();
        $buku    = $this->getBukuTersedia();

        $this->actingAs($user)->post(route('peminjaman.store'), [
            'anggota_id' => $anggota->id,
            'buku_ids'   => [$buku->id],
        ]);

        $peminjaman = Peminjaman::first();
        $stokSetelahPinjam = $buku->fresh()->jumlah_tersedia;

        $this->actingAs($user)->delete(route('peminjaman.destroy', $peminjaman->id));

        $this->assertEquals($stokSetelahPinjam + 1, $buku->fresh()->jumlah_tersedia);
    }

    // ==================== PBI-022: NOTIFIKASI EMAIL ====================

    public function test_notifikasi_email_pengingat_terkirim(): void
    {
        Notification::fake();

        $user    = $this->getPustakawanUser();
        $anggota = $this->getAnggotaAktif();
        $buku    = $this->getBukuTersedia();

        // Buat peminjaman yang jatuh tempo 2 hari lagi
        Peminjaman::create([
            'anggota_id'          => $anggota->id,
            'pustakawan_id'       => $user->id,
            'tanggal_pinjam'      => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(2)->toDateString(),
            'status'              => 'dipinjam',
        ]);

        $peminjaman = Peminjaman::first();
        DetailPeminjaman::create([
            'peminjaman_id' => $peminjaman->id,
            'buku_id'       => $buku->id,
            'jumlah'        => 1,
        ]);

        // Jalankan command
        $this->artisan('peminjaman:kirim-pengingat')
             ->expectsOutput('Email terkirim ke: ' . $anggota->user->email)
             ->assertExitCode(0);

        Notification::assertSentTo(
            $anggota->user,
            \App\Notifications\PengingatJatuhTempo::class
        );
    }

    // ==================== AUTHORIZATION ====================

    public function test_anggota_tidak_bisa_akses_peminjaman(): void
    {
        $role = Role::where('name', 'anggota')->first();
        $user = User::factory()->create([
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('peminjaman.index'));
        $response->assertStatus(403);
    }

    public function test_guest_tidak_bisa_akses_peminjaman(): void
    {
        $response = $this->get(route('peminjaman.index'));
        $response->assertRedirect(route('login'));
    }
}