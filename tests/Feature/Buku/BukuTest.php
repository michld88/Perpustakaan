<?php

namespace Tests\Feature\Buku;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Penerbit;
use App\Models\Penulis;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BukuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\AdminSeeder::class);
        $this->seed(\Database\Seeders\BukuSeeder::class);
    }

    private function getPustakawanUser(): User
    {
        $pustakawanRole = Role::where('name', 'pustakawan')->first();
        return User::factory()->create([
            'role_id'   => $pustakawanRole->id,
            'is_active' => true,
        ]);
    }

    // ==================== PBI-008: CRUD TESTS ====================

    /** @test */
    public function pustakawan_can_view_buku_index(): void
    {
        $user = $this->getPustakawanUser();

        $response = $this->actingAs($user)->get(route('buku.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Buku/Index'));
    }

    /** @test */
    public function pustakawan_can_view_create_form(): void
    {
        $user = $this->getPustakawanUser();

        $response = $this->actingAs($user)->get(route('buku.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Buku/Create'));
    }

    /** @test */
    public function pustakawan_can_store_new_buku(): void
    {
        $user = $this->getPustakawanUser();
        $kategori = Kategori::first();
        $penerbit = Penerbit::first();
        $penulis = Penulis::first();

        $data = [
            'judul'            => 'Buku Test Baru',
            'isbn'             => '978-999-888-777-1',
            'kategori_id'      => $kategori->id,
            'penerbit_id'      => $penerbit->id,
            'tahun_terbit'     => 2023,
            'jumlah_eksemplar' => 5,
            'deskripsi'        => 'Deskripsi buku test',
            'status'           => 'tersedia',
            'penulis_id'       => [$penulis->id],
        ];

        $response = $this->actingAs($user)->post(route('buku.store'), $data);

        $response->assertRedirect(route('buku.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('buku', [
            'judul' => 'Buku Test Baru',
            'isbn'  => '978-999-888-777-1',
        ]);
    }

    /** @test */
    public function pustakawan_can_view_buku_detail(): void
    {
        $user = $this->getPustakawanUser();
        $buku = Buku::first();

        $response = $this->actingAs($user)->get(route('buku.show', $buku->id));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Buku/Show')
            ->has('buku')
        );
    }

    /** @test */
    public function pustakawan_can_view_edit_form(): void
    {
        $user = $this->getPustakawanUser();
        $buku = Buku::first();

        $response = $this->actingAs($user)->get(route('buku.edit', $buku->id));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Buku/Edit'));
    }

    /** @test */
    public function pustakawan_can_update_buku(): void
    {
        $user = $this->getPustakawanUser();
        $buku = Buku::first();
        $kategori = Kategori::first();
        $penerbit = Penerbit::first();
        $penulis = Penulis::first();

        $data = [
            'judul'            => 'Judul Diupdate',
            'isbn'             => $buku->isbn, // ISBN sama
            'kategori_id'      => $kategori->id,
            'penerbit_id'      => $penerbit->id,
            'tahun_terbit'     => 2022,
            'jumlah_eksemplar' => 10,
            'deskripsi'        => 'Deskripsi update',
            'status'           => 'tersedia',
            'penulis_id'       => [$penulis->id],
        ];

        $response = $this->actingAs($user)->put(route('buku.update', $buku->id), $data);

        $response->assertRedirect(route('buku.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('buku', [
            'id'    => $buku->id,
            'judul' => 'Judul Diupdate',
        ]);
    }

    /** @test */
    public function pustakawan_can_delete_buku(): void
    {
        $user = $this->getPustakawanUser();
        $buku = Buku::first();
        $bukuId = $buku->id;

        $response = $this->actingAs($user)->delete(route('buku.destroy', $bukuId));

        $response->assertRedirect(route('buku.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('buku', ['id' => $bukuId]);
    }

    // ==================== PBI-009: PENCARIAN TESTS ====================

    /** @test */
    public function can_search_buku_by_judul(): void
    {
        $user = $this->getPustakawanUser();

        $response = $this->actingAs($user)->get(route('buku.index', ['search' => 'Algoritma']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('buku.data')
            ->where('filters.search', 'Algoritma')
        );
    }

    /** @test */
    public function can_search_buku_by_isbn(): void
    {
        $user = $this->getPustakawanUser();
        $buku = Buku::first();

        $response = $this->actingAs($user)->get(route('buku.index', ['search' => $buku->isbn]));

        $response->assertStatus(200);
    }

    // ==================== PBI-010: FILTER & SORTING TESTS ====================

    /** @test */
    public function can_filter_buku_by_status(): void
    {
        $user = $this->getPustakawanUser();

        $response = $this->actingAs($user)->get(route('buku.index', ['status' => 'tersedia']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->where('filters.status', 'tersedia'));
    }

    /** @test */
    public function can_filter_buku_by_kategori(): void
    {
        $user = $this->getPustakawanUser();
        $kategori = Kategori::first();

        $response = $this->actingAs($user)->get(route('buku.index', ['kategori_id' => $kategori->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->where('filters.kategori_id', (string) $kategori->id));
        // atau gunakan: ->where('filters.kategori_id', $kategori->id . '')
    }

    /** @test */
    public function can_sort_buku_by_judul_asc(): void
    {
        $user = $this->getPustakawanUser();

        $response = $this->actingAs($user)->get(route('buku.index', ['sort' => 'judul', 'dir' => 'asc']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.sort', 'judul')
            ->where('filters.dir', 'asc')
        );
    }

    /** @test */
    public function can_sort_buku_by_tahun_desc(): void
    {
        $user = $this->getPustakawanUser();

        $response = $this->actingAs($user)->get(route('buku.index', ['sort' => 'tahun_terbit', 'dir' => 'desc']));

        $response->assertStatus(200);
    }

    // ==================== VALIDATION TESTS ====================

    /** @test */
    public function cannot_store_buku_without_required_fields(): void
    {
        $user = $this->getPustakawanUser();

        $response = $this->actingAs($user)->post(route('buku.store'), []);

        $response->assertSessionHasErrors(['judul', 'isbn', 'kategori_id', 'penerbit_id', 'tahun_terbit', 'jumlah_eksemplar', 'penulis_id']);
    }

    /** @test */
    public function cannot_store_buku_with_duplicate_isbn(): void
    {
        $user = $this->getPustakawanUser();
        $existingBuku = Buku::first();

        $kategori = Kategori::first();
        $penerbit = Penerbit::first();
        $penulis = Penulis::first();

        $data = [
            'judul'            => 'Buku Duplikat',
            'isbn'             => $existingBuku->isbn, // ISBN yang sudah ada
            'kategori_id'      => $kategori->id,
            'penerbit_id'      => $penerbit->id,
            'tahun_terbit'     => 2023,
            'jumlah_eksemplar' => 5,
            'status'           => 'tersedia',
            'penulis_id'       => [$penulis->id],
        ];

        $response = $this->actingAs($user)->post(route('buku.store'), $data);

        $response->assertSessionHasErrors(['isbn']);
    }

    // ==================== AUTHORIZATION TESTS ====================

    /** @test */
    public function anggota_cannot_access_buku_management(): void
    {
        $role    = Role::where('name', 'anggota')->first();
        $anggota = User::factory()->create([
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        // Anggota BISA lihat katalog dan detail
        $this->actingAs($anggota)->get(route('buku.index'))->assertStatus(200);

        // Anggota TIDAK BISA create, edit, delete
        $this->actingAs($anggota)->get(route('buku.create'))->assertStatus(403);

        $buku = Buku::first();
        if ($buku) {
            $this->actingAs($anggota)->get(route('buku.edit', $buku->id))->assertStatus(403);
            $this->actingAs($anggota)->delete(route('buku.destroy', $buku->id))->assertStatus(403);
        }
    }

    /** @test */
    public function guest_cannot_access_buku_management(): void
    {
        $response = $this->get(route('buku.index'));

        $response->assertRedirect(route('login'));
    }
}