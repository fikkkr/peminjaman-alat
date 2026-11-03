<?php

namespace Tests\Feature;

use App\Models\Peminjamans;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsersCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_user_list_and_form(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['name' => 'Akun Uji', 'role' => 'peminjam']);

        $this->actingAs($admin)->get(route('users.index'))->assertOk()->assertSee('Akun Uji');
        $this->actingAs($admin)->get(route('users.create'))->assertOk();
        $this->actingAs($admin)->get(route('users.edit', $target))->assertOk()->assertSee('Akun Uji');
    }

    public function test_admin_can_create_user_and_password_is_hashed(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Pengguna Baru',
            'email' => 'baru@simpa.test',
            'password' => 'password-awal',
            'role' => 'petugas',
        ])->assertRedirect(route('users.index'));

        $user = User::where('email', 'baru@simpa.test')->firstOrFail();
        $this->assertSame('petugas', $user->role);
        $this->assertNotSame('password-awal', $user->password);
        $this->assertTrue(Hash::check('password-awal', $user->password));
    }

    public function test_user_creation_rejects_duplicate_email_and_invalid_role_or_password(): void
    {
        $admin = $this->admin();
        User::factory()->create(['email' => 'sudah-ada@simpa.test']);

        $this->actingAs($admin)->from(route('users.create'))->post(route('users.store'), [
            'name' => 'User Duplikat',
            'email' => 'sudah-ada@simpa.test',
            'password' => 'pendek',
            'role' => 'superuser',
        ])->assertRedirect(route('users.create'))
            ->assertSessionHasErrors(['email', 'password', 'role']);

        $this->assertDatabaseMissing('users', ['name' => 'User Duplikat']);
    }

    public function test_admin_can_update_user_and_keep_existing_password_when_password_is_blank(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create([
            'name' => 'Nama Lama',
            'email' => 'lama@simpa.test',
            'password' => 'password-lama',
            'role' => 'peminjam',
        ]);
        $oldHash = $target->password;

        $this->actingAs($admin)->put(route('users.update', $target), [
            'name' => 'Nama Baru',
            'email' => 'baru@simpa.test',
            'password' => '',
            'role' => 'petugas',
        ])->assertRedirect(route('users.index'));

        $target->refresh();
        $this->assertSame('Nama Baru', $target->name);
        $this->assertSame('baru@simpa.test', $target->email);
        $this->assertSame('petugas', $target->role);
        $this->assertSame($oldHash, $target->password);
        $this->assertTrue(Hash::check('password-lama', $target->password));
    }

    public function test_admin_can_change_user_password_and_short_password_is_rejected(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['password' => 'password-lama', 'role' => 'peminjam']);

        $this->actingAs($admin)->put(route('users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'password' => 'password-baru',
            'role' => $target->role,
        ])->assertRedirect(route('users.index'));

        $target->refresh();
        $this->assertTrue(Hash::check('password-baru', $target->password));
        $this->assertFalse(Hash::check('password-lama', $target->password));

        $this->actingAs($admin)->from(route('users.edit', $target))
            ->put(route('users.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'password' => 'singkat',
                'role' => $target->role,
            ])->assertRedirect(route('users.edit', $target))
            ->assertSessionHasErrors('password');
    }

    public function test_admin_can_delete_unrelated_user_but_not_self_or_user_with_loans(): void
    {
        $admin = $this->admin();
        $deletable = User::factory()->create(['role' => 'peminjam']);
        $this->actingAs($admin)->delete(route('users.destroy', $deletable))
            ->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $deletable->id]);

        $this->actingAs($admin)->delete(route('users.destroy', $admin))->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $borrower = User::factory()->create(['role' => 'peminjam']);
        Peminjamans::create([
            'user_id' => $borrower->id,
            'tanggal_pinjam' => today(),
            'status_peminjaman' => 'menunggu',
        ]);
        $this->actingAs($admin)->from(route('users.index'))
            ->delete(route('users.destroy', $borrower))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $borrower->id]);
    }
}