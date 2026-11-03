<?php

namespace Tests\Feature;

use App\Models\Alats;
use App\Models\DetailPeminjamans;
use App\Models\Kategoris;
use App\Models\Peminjamans;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_specific_pages_are_accessible_to_their_intended_roles(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $peminjam = User::factory()->create(['role' => 'peminjam']);

        $this->actingAs($admin)->get('/users')->assertOk();
        $this->actingAs($admin)->get('/kategoris')->assertOk();
        $this->actingAs($admin)->get('/alats')->assertOk();
        $this->actingAs($admin)->get('/log-aktivitas')->assertOk();

        $this->actingAs($petugas)->get('/persetujuan')->assertOk();
        $this->actingAs($petugas)->get('/laporan')->assertOk();

        $this->actingAs($peminjam)->get('/daftar-alat')->assertOk();
        $this->actingAs($peminjam)->get('/peminjaman')->assertOk();
        $this->actingAs($peminjam)->get('/detail-peminjaman')->assertOk();
        $this->actingAs($peminjam)->get('/pengembalian')->assertOk();
    }

    public function test_guest_is_redirected_from_authenticated_pages_and_mutations(): void
    {
        foreach ([
            ['GET', '/dashboard'], ['GET', '/users'], ['GET', '/persetujuan'],
            ['GET', '/laporan'], ['GET', '/daftar-alat'], ['GET', '/detail-peminjaman'],
            ['POST', '/logout'], ['POST', '/persetujuan/1/setujui'],
            ['POST', '/persetujuan/1/tolak'], ['POST', '/peminjaman'],
            ['POST', '/detail-peminjaman/1/pengembalian'],
            ['PUT', '/users/1'], ['PATCH', '/users/1'], ['DELETE', '/users/1'],
        ] as [$method, $uri]) {
            $this->call($method, $uri)->assertRedirect('/login');
        }
    }

    public function test_peminjam_and_petugas_cannot_access_admin_routes_or_mutations(): void
    {
        $peminjam = User::factory()->create(['role' => 'peminjam']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ([$peminjam, $petugas] as $user) {
            $this->actingAs($user)->get('/users')->assertForbidden();
            $this->actingAs($user)->get('/kategoris')->assertForbidden();
            $this->actingAs($user)->get('/alats')->assertForbidden();
            $this->actingAs($user)->get('/log-aktivitas')->assertForbidden();
            $this->actingAs($user)->post('/users', [])->assertForbidden();
            $this->actingAs($user)->put('/users/'.$admin->id, [])->assertForbidden();
            $this->actingAs($user)->patch('/users/'.$admin->id, [])->assertForbidden();
            $this->actingAs($user)->delete('/users/'.$admin->id)->assertForbidden();
        }
    }

    public function test_peminjam_cannot_access_petugas_routes_or_mutations(): void
    {
        $peminjam = User::factory()->create(['role' => 'peminjam']);
        $this->actingAs($peminjam)->get('/persetujuan')->assertForbidden();
        $this->actingAs($peminjam)->get('/laporan')->assertForbidden();
        $staff = User::factory()->create(['role' => 'peminjam']);
        $loan = Peminjamans::create(['user_id' => $staff->id, 'tanggal_pinjam' => today(), 'status_peminjaman' => 'menunggu']);
        $this->actingAs($peminjam)->post('/persetujuan/'.$loan->id.'/setujui')->assertForbidden();
        $this->actingAs($peminjam)->post('/persetujuan/'.$loan->id.'/tolak')->assertForbidden();
    }

    public function test_peminjam_only_sees_own_details_and_cannot_return_another_users_item(): void
    {
        $owner = User::factory()->create(['role' => 'peminjam']);
        $other = User::factory()->create(['role' => 'peminjam']);
        $category = Kategoris::create(['nama_kategori' => 'Kategori isolasi']);
        $tool = Alats::create(['kategori_id' => $category->id, 'nama_alat' => 'Alat Saya', 'stok' => 2]);
        $otherTool = Alats::create(['kategori_id' => $category->id, 'nama_alat' => 'Alat Rahasia', 'stok' => 2]);

        $ownLoan = Peminjamans::create(['user_id' => $owner->id, 'tanggal_pinjam' => today(), 'status_peminjaman' => 'disetujui']);
        $ownLoan->details()->create(['alat_id' => $tool->id, 'jumlah' => 1, 'status' => false]);
        $otherLoan = Peminjamans::create(['user_id' => $other->id, 'tanggal_pinjam' => today(), 'status_peminjaman' => 'disetujui']);
        $otherDetail = $otherLoan->details()->create(['alat_id' => $otherTool->id, 'jumlah' => 1, 'status' => false]);

        $this->actingAs($owner)->get('/detail-peminjaman')
            ->assertOk()->assertSee('Alat Saya')->assertDontSee('Alat Rahasia')
            ->assertDontSee('data-detail-id="'.$otherDetail->id.'"');
        $this->actingAs($owner)->post('/detail-peminjaman/'.$otherDetail->id.'/pengembalian')->assertForbidden();
        $this->assertDatabaseMissing('pengembalians', ['detail_peminjaman_id' => $otherDetail->id]);
        $this->assertDatabaseHas('detail_peminjamans', ['id' => $otherDetail->id, 'status' => false]);
    }

    public function test_peminjam_cannot_assign_a_new_loan_to_another_user_by_request_parameter(): void
    {
        $owner = User::factory()->create(['role' => 'peminjam']);
        $other = User::factory()->create(['role' => 'peminjam']);
        $category = Kategoris::create(['nama_kategori' => 'Kategori request']);
        $tool = Alats::create(['kategori_id' => $category->id, 'nama_alat' => 'Alat request', 'stok' => 3]);

        $this->actingAs($owner)->post('/peminjaman', [
            'user_id' => $other->id,
            'tanggal_pinjam' => today()->toDateString(),
            'items' => [['alat_id' => $tool->id, 'jumlah' => 1]],
        ])->assertRedirect(route('peminjaman.index'));

        $this->assertDatabaseHas('peminjamans', ['user_id' => $owner->id]);
        $this->assertDatabaseMissing('peminjamans', ['user_id' => $other->id]);
    }
}
