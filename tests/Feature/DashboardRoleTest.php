<?php
namespace Tests\Feature;
use App\Models\Alats;
use App\Models\DetailPeminjamans;
use App\Models\Kategoris;
use App\Models\Peminjamans;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class DashboardRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_uses_live_inventory_and_transaction_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Kategoris::create(['nama_kategori' => 'Peralatan Uji', 'deskripsi' => 'Untuk tes dashboard']);
        Alats::create(['kategori_id' => $category->id, 'nama_alat' => 'Bor Uji', 'stok' => 7]);
        Peminjamans::create(['user_id' => $admin->id, 'tanggal_pinjam' => today(), 'status_peminjaman' => 'menunggu']);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('Total Alat')->assertSee('Total Stok')->assertSee('Peralatan Uji')->assertSee('Menunggu');
    }

    public function test_petugas_dashboard_shows_pending_applications(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $peminjam = User::factory()->create(['name' => 'Pemohon Dashboard', 'role' => 'peminjam']);
        $category = Kategoris::create(['nama_kategori' => 'Kategori Petugas']);
        $alat = Alats::create(['kategori_id' => $category->id, 'nama_alat' => 'Mikroskop Uji', 'stok' => 3]);
        $loan = Peminjamans::create(['user_id' => $peminjam->id, 'tanggal_pinjam' => today(), 'tanggal_kembali_rencana' => today()->addDays(4), 'status_peminjaman' => 'menunggu']);
        $loan->details()->create(['alat_id' => $alat->id, 'jumlah' => 1, 'status' => false]);

        $this->actingAs($petugas)->get(route('dashboard'))->assertOk()->assertSee('Pengajuan Menunggu Persetujuan')->assertSee('Pemohon Dashboard')->assertSee('Jenis Alat');
    }

    public function test_peminjam_dashboard_only_shows_their_own_items(): void
    {
        $owner = User::factory()->create(['role' => 'peminjam']);
        $other = User::factory()->create(['role' => 'peminjam']);
        $category = Kategoris::create(['nama_kategori' => 'Kategori Peminjam']);
        $ownTool = Alats::create(['kategori_id' => $category->id, 'nama_alat' => 'Alat Milik Saya', 'stok' => 2]);
        $otherTool = Alats::create(['kategori_id' => $category->id, 'nama_alat' => 'Alat Rahasia Pengguna Lain', 'stok' => 4]);
        $ownLoan = Peminjamans::create(['user_id' => $owner->id, 'tanggal_pinjam' => today(), 'status_peminjaman' => 'disetujui']);
        $ownLoan->details()->create(['alat_id' => $ownTool->id, 'jumlah' => 1, 'status' => false]);
        $otherLoan = Peminjamans::create(['user_id' => $other->id, 'tanggal_pinjam' => today(), 'status_peminjaman' => 'disetujui']);
        $otherLoan->details()->create(['alat_id' => $otherTool->id, 'jumlah' => 1, 'status' => false]);

        $this->actingAs($owner)->get(route('dashboard'))->assertOk()->assertSee('Alat Milik Saya')->assertDontSee('Alat Rahasia Pengguna Lain');
    }

    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}


