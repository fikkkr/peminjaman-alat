<?php

namespace Tests\Feature;

use App\Models\Alats;
use App\Models\Kategoris;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategorisCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_category_list_and_create_category(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('kategoris.index'))->assertOk();
        $this->actingAs($admin)->get(route('kategoris.create'))->assertOk();

        $this->actingAs($admin)->post(route('kategoris.store'), [
            'nama_kategori' => 'Perkakas Laboratorium',
            'deskripsi' => 'Alat untuk kegiatan laboratorium',
        ])->assertRedirect(route('kategoris.index'));

        $this->assertDatabaseHas('kategoris', [
            'nama_kategori' => 'Perkakas Laboratorium',
            'deskripsi' => 'Alat untuk kegiatan laboratorium',
        ]);
        $this->actingAs($admin)->get(route('kategoris.index'))->assertSee('Perkakas Laboratorium');
    }

    public function test_category_creation_rejects_invalid_data(): void
    {
        $this->actingAs($this->admin())->from(route('kategoris.create'))
            ->post(route('kategoris.store'), ['nama_kategori' => str_repeat('x', 256)])
            ->assertRedirect(route('kategoris.create'))
            ->assertSessionHasErrors(['nama_kategori']);

        $this->assertDatabaseCount('kategoris', 0);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = $this->admin();
        $category = Kategoris::create(['nama_kategori' => 'Kategori Lama', 'deskripsi' => 'Deskripsi lama']);

        $this->actingAs($admin)->get(route('kategoris.edit', $category))->assertOk()->assertSee('Kategori Lama');
        $this->actingAs($admin)->put(route('kategoris.update', $category), [
            'nama_kategori' => 'Kategori Baru',
            'deskripsi' => 'Deskripsi baru',
        ])->assertRedirect(route('kategoris.index'));

        $this->assertDatabaseHas('kategoris', [
            'id' => $category->id,
            'nama_kategori' => 'Kategori Baru',
            'deskripsi' => 'Deskripsi baru',
        ]);
    }

    public function test_admin_can_delete_category_without_tools(): void
    {
        $admin = $this->admin();
        $category = Kategoris::create(['nama_kategori' => 'Kategori Hapus']);

        $this->actingAs($admin)->delete(route('kategoris.destroy', $category))
            ->assertRedirect(route('kategoris.index'));
        $this->assertDatabaseMissing('kategoris', ['id' => $category->id]);
    }

    public function test_category_in_use_by_tools_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $category = Kategoris::create(['nama_kategori' => 'Kategori Terpakai']);
        Alats::create(['kategori_id' => $category->id, 'nama_alat' => 'Alat terkait', 'stok' => 4]);

        $this->actingAs($admin)->from(route('kategoris.index'))
            ->delete(route('kategoris.destroy', $category))
            ->assertRedirect(route('kategoris.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kategoris', ['id' => $category->id]);
    }
}