<?php
$content = <<<'PHPCODE'
<?php

namespace Tests\Feature\Anggota;

use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnggotaTest extends TestCase
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

    private function getAnggotaData(): array
    {
        return [
            'name'     => 'Anggota Test',
            'email'    => 'anggota.test@email.com',
            'password' => 'password123',
            'nim_nip'  => '12345678',
            'alamat'   => 'Jl. Test No. 1',
            'telepon'  => '08123456789',
        ];
    }

    public function test_pustakawan_can_view_anggota_index(): void
    {
        $user = $this->getPustakawanUser();
        $response = $this->actingAs($user)->get(route('anggota.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Anggota/Index'));
    }

    public function test_pustakawan_can_view_create_form(): void
    {
        $user = $this->getPustakawanUser();
        $response = $this->actingAs($user)->get(route('anggota.create'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Anggota/Create'));
    }

    public function test_pustakawan_can_register_new_anggota(): void
    {
        Storage::fake('public');
        $user = $this->getPustakawanUser();
        $response = $this->actingAs($user)->post(route('anggota.store'), $this->getAnggotaData());
        $response->assertRedirect(route('anggota.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'anggota.test@email.com']);
        $this->assertDatabaseHas('anggota', ['nim_nip' => '12345678']);
    }

    public function test_pustakawan_can_register_anggota_with_foto(): void
    {
        Storage::fake('public');
        $user = $this->getPustakawanUser();
        $data = $this->getAnggotaData();
        $data['foto'] = UploadedFile::fake()->image('foto.jpg', 200, 200);
        $response = $this->actingAs($user)->post(route('anggota.store'), $data);
        $response->assertRedirect(route('anggota.index'));
        $this->assertDatabaseHas('anggota', ['nim_nip' => '12345678']);
    }

    public function test_cannot_register_anggota_without_required_fields(): void
    {
        $user = $this->getPustakawanUser();
        $response = $this->actingAs($user)->post(route('anggota.store'), []);
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_cannot_register_anggota_with_duplicate_email(): void
    {
        Storage::fake('public');
        $user = $this->getPustakawanUser();
        $this->actingAs($user)->post(route('anggota.store'), $this->getAnggotaData());
        $data = $this->getAnggotaData();
        $data['nim_nip'] = '99999999';
        $response = $this->actingAs($user)->post(route('anggota.store'), $data);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_pustakawan_can_view_edit_form(): void
    {
        Storage::fake('public');
        $user = $this->getPustakawanUser();
        $this->actingAs($user)->post(route('anggota.store'), $this->getAnggotaData());
        $anggota = Anggota::with(['user'])->first();
        $response = $this->actingAs($user)->get(route('anggota.edit', $anggota->id));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Anggota/Edit'));
    }

    public function test_pustakawan_can_update_anggota(): void
    {
        Storage::fake('public');
        $user = $this->getPustakawanUser();
        $this->actingAs($user)->post(route('anggota.store'), $this->getAnggotaData());
        $anggota = Anggota::with(['user'])->first();

        $response = $this->actingAs($user)->put(route('anggota.update', $anggota->id), [
            'name'    => 'Nama Diupdate',
            'email'   => $anggota->user->email,
            'nim_nip' => $anggota->nim_nip,
            'alamat'  => 'Alamat Baru',
            'telepon' => '08999999999',
            'status'  => 'aktif',
        ]);

        $response->assertRedirect(route('anggota.index'));
        $this->assertDatabaseHas('users', ['name' => 'Nama Diupdate']);
    }

    public function test_pustakawan_can_nonaktifkan_anggota(): void
    {
        Storage::fake('public');
        $user = $this->getPustakawanUser();
        $this->actingAs($user)->post(route('anggota.store'), $this->getAnggotaData());
        $anggota = Anggota::with(['user'])->first();

        $response = $this->actingAs($user)->put(route('anggota.update', $anggota->id), [
            'name'    => $anggota->user->name,
            'email'   => $anggota->user->email,
            'nim_nip' => $anggota->nim_nip,
            'status'  => 'nonaktif',
        ]);

        $response->assertRedirect(route('anggota.index'));
        $this->assertDatabaseHas('anggota', ['id' => $anggota->id, 'status' => 'nonaktif']);
    }

    public function test_pustakawan_can_delete_anggota(): void
    {
        Storage::fake('public');
        $user = $this->getPustakawanUser();
        $this->actingAs($user)->post(route('anggota.store'), $this->getAnggotaData());
        $anggota = Anggota::with(['user'])->first();
        $anggotaId = $anggota->id;

        $response = $this->actingAs($user)->delete(route('anggota.destroy', $anggotaId));

        $response->assertRedirect(route('anggota.index'));
        $this->assertDatabaseMissing('anggota', ['id' => $anggotaId]);
    }

    public function test_pustakawan_can_cetak_kartu_anggota(): void
    {
        Storage::fake('public');
        $user = $this->getPustakawanUser();
        $this->actingAs($user)->post(route('anggota.store'), $this->getAnggotaData());
        $anggota = Anggota::with(['user'])->first();
        $response = $this->actingAs($user)->get(route('anggota.cetak-kartu', $anggota->id));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Anggota/CetakKartu'));
        $this->assertDatabaseHas('kartu_anggota', ['anggota_id' => $anggota->id]);
    }

    public function test_nomor_kartu_tidak_duplikat(): void
    {
        Storage::fake('public');
        $user = $this->getPustakawanUser();
        $this->actingAs($user)->post(route('anggota.store'), $this->getAnggotaData());
        $anggota = Anggota::with(['user'])->first();
        $this->actingAs($user)->get(route('anggota.cetak-kartu', $anggota->id));
        $this->actingAs($user)->get(route('anggota.cetak-kartu', $anggota->id));
        $this->assertEquals(1, \App\Models\KartuAnggota::where('anggota_id', $anggota->id)->count());
    }

    public function test_anggota_cannot_access_manajemen_anggota(): void
    {
        $role = Role::where('name', 'anggota')->first();
        $anggotaUser = User::factory()->create([
            'role_id'   => $role->id,
            'is_active' => true,
        ]);
        $response = $this->actingAs($anggotaUser)->get(route('anggota.index'));
        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_manajemen_anggota(): void
    {
        $response = $this->get(route('anggota.index'));
        $response->assertRedirect(route('login'));
    }
}
PHPCODE;

file_put_contents('tests/Feature/Anggota/AnggotaTest.php', $content);
echo 'Berhasil: ' . strlen($content) . ' bytes' . PHP_EOL;
echo 'test_ methods: ' . substr_count($content, 'public function test_') . ' methods' . PHP_EOL;