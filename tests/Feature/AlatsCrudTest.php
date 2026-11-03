<?php

namespace Tests\Feature;

use App\Models\Alats;
use App\Models\Kategoris;
use App\Models\Peminjamans;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlatsCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function category(string $name = 'Kategori Alat'): Kategoris
    {
        return Kategoris::create(['nama_kategori' => $name]);
    }

    public function test_admin_can_view_tool_list_and_create_tool_with_category(): void
    {
        $admin = $this->admin();
        $category = $this->category();
        $this->actingAs($admin)->get(route('alats.index'))->assertOk();
        $this->actingAs($admin)->get(route('alats.create'))->assertOk()->assertSee($category->nama_kategori);

        $this->actingAs($admin)->post(route('alats.store'), [
            'kategori_id' => $category->id,
            'nama_alat' => 'Mikroskop',
            'stok' => 8,
        ])->assertRedirect(route('alats.index'));

        $this->assertDatabaseHas('alats', [
            'kategori_id' => $category->id,
            'nama_alat' => 'Mikroskop',
            'stok' => 8,
        ]);
        $this->actingAs($admin)->get(route('alats.index'))->assertSee('Mikroskop');
    }

    public function test_tool_creation_rejects_missing_or_invalid_fields(): void
    {
        $this->actingAs($this->admin())->from(route('alats.create'))->post(route('alats.store'), [
            'kategori_id' => 999,
            'nama_alat' => '',
            'stok' => -1,
        ])->assertRedirect(route('alats.create'))
            ->assertSessionHasErrors(['kategori_id', 'nama_alat', 'stok']);

        $this->assertDatabaseCount('alats', 0);
    }

    public function test_admin_can_update_tool_details_and_category_without_changing_stock(): void
    {
        $admin = $this->admin();
        $firstCategory = $this->category('Kategori Awal');
        $secondCategory = $this->category('Kategori Baru');
        $tool = Alats::create([
            'kategori_id' => $firstCategory->id,
            'nama_alat' => 'Bor Lama',
            'stok' => 7,
        ]);

        $this->actingAs($admin)->get(route('alats.edit', $tool))->assertOk()->assertSee('Bor Lama');
        $this->actingAs($admin)->put(route('alats.update', $tool), [
            'kategori_id' => $secondCategory->id,
            'nama_alat' => 'Bor Baru',
            'stok' => 7,
        ])->assertRedirect(route('alats.index'));

        $this->assertDatabaseHas('alats', [
            'id' => $tool->id,
            'kategori_id' => $secondCategory->id,
            'nama_alat' => 'Bor Baru',
            'stok' => 7,
        ]);
    }

    public function test_admin_can_delete_tool_without_transaction_details(): void
    {
        $admin = $this->admin();
        $tool = Alats::create([
            'kategori_id' => $this->category()->id,
            'nama_alat' => 'Alat tanpa transaksi',
            'stok' => 0,
        ]);

        $this->actingAs($admin)->delete(route('alats.destroy', $tool))
            ->assertRedirect(route('alats.index'));
        $this->assertDatabaseMissing('alats', ['id' => $tool->id]);
    }

    public function test_tool_with_transaction_details_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['role' => 'peminjam']);
        $tool = Alats::create([
            'kategori_id' => $this->category()->id,
            'nama_alat' => 'Alat bertransaksi',
            'stok' => 1,
        ]);
        $loan = Peminjamans::create([
            'user_id' => $user->id,
            'tanggal_pinjam' => today(),
            'status_peminjaman' => 'disetujui',
        ]);
        $loan->details()->create(['alat_id' => $tool->id, 'jumlah' => 1, 'status' => false]);

        $this->actingAs($admin)->from(route('alats.index'))
            ->delete(route('alats.destroy', $tool))
            ->assertRedirect(route('alats.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('alats', ['id' => $tool->id, 'stok' => 1]);
    }
}