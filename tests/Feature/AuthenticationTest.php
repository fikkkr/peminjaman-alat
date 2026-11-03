<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_can_log_in_and_is_redirected_to_role_aware_dashboard(): void
    {
        foreach (['admin', 'petugas', 'peminjam'] as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'email' => $role.'@simpa.test',
                'password' => 'password-rahasia',
            ]);

            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'password-rahasia',
            ]);

            // The application redirects all roles to the dashboard, whose content is role-specific.
            $response->assertRedirect(route('dashboard'));
            $this->assertAuthenticatedAs($user);
            $this->get(route('dashboard'))->assertOk();
            $this->post('/logout')->assertRedirect(route('login'));
            $this->assertGuest();
        }
    }

    public function test_login_fails_for_wrong_password_and_unknown_email(): void
    {
        $user = User::factory()->create(['password' => 'password-benar']);

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password-salah',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->from('/login')->post('/login', [
            'email' => 'tidak-ada@simpa.test',
            'password' => 'password-apapun',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_session_id_is_regenerated_on_login_and_session_is_invalidated_on_logout(): void
    {
        $user = User::factory()->create(['password' => 'password-rahasia']);
        $this->get('/login');
        $oldSessionId = session()->getId();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password-rahasia',
        ])->assertRedirect(route('dashboard'));

        $newSessionId = session()->getId();
        $this->assertNotSame($oldSessionId, $newSessionId, 'Login should regenerate the session ID.');
        $this->withSession(['session_marker' => 'private-value']);
        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertNotSame($newSessionId, session()->getId(), 'Logout should invalidate the old session.');
        $this->assertGuest();
    }
}
