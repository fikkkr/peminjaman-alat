<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_registration_form(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Buat Akun SIMPA')
            ->assertSee('password_confirmation');
    }

    public function test_registration_creates_peminjam_account_and_authenticates_user(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Peminjam Baru',
            'email' => 'peminjam.baru@simpa.test',
            'password' => 'password-aman',
            'password_confirmation' => 'password-aman',
            'role' => 'admin',
        ]);

        $user = User::where('email', 'peminjam.baru@simpa.test')->firstOrFail();

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('peminjam', $user->role);
        $this->assertTrue(Hash::check('password-aman', $user->password));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'peminjam',
        ]);
    }

    public function test_registration_rejects_duplicate_email_and_unconfirmed_password(): void
    {
        User::factory()->create(['email' => 'terdaftar@simpa.test']);

        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Tidak Dibuat',
            'email' => 'terdaftar@simpa.test',
            'password' => 'password-aman',
            'password_confirmation' => 'password-berbeda',
        ])->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['name' => 'Tidak Dibuat']);
    }

    public function test_login_page_links_to_registration(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('register'));
    }
}