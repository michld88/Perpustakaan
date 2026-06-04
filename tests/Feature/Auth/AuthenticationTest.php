<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $user = User::factory()->create([
            'role_id'   => $adminRole->id,
            'is_active' => true,
            'password'  => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect();
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $user = User::factory()->create([
            'role_id'   => $adminRole->id,
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $user = User::factory()->create([
            'role_id'   => $adminRole->id,
            'is_active' => false,
            'password'  => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $user = User::factory()->create([
            'role_id'   => $adminRole->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $user = User::factory()->create([
            'role_id'   => $adminRole->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    public function test_anggota_cannot_access_admin_dashboard(): void
    {
        $anggotaRole = Role::where('name', 'anggota')->first();
        $user = User::factory()->create([
            'role_id'   => $anggotaRole->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(403);
    }
}