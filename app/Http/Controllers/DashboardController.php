<?php
namespace App\Http\Controllers;
use App\Models\Alats;
use App\Models\DetailPeminjamans;
use App\Models\Kategoris;
use App\Models\LogAktivitas;
use App\Models\Peminjamans;
use App\Models\User;
class DashboardController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 2;

    public function index()
    {
        $user = auth()->user();
        return match ($user->role) {
            'admin' => $this->adminDashboard(),
            'petugas' => $this->petugasDashboard(),
            'peminjam' => $this->peminjamDashboard($user->id),
            default => abort(403, 'Anda tidak memiliki akses ke halaman ini.'),
        };
    }

    private function adminDashboard()
    {
        $approved = Peminjamans::where('status_peminjaman', 'disetujui');
        $completed = (clone $approved)->whereHas('details')->whereDoesntHave('details', fn ($query) => $query->where('status', false));
        return view('dashboard.index', [
            'role' => 'admin',
            'stats' => [
                ['label' => 'Total Alat', 'value' => Alats::count(), 'note' => 'Jenis alat terdaftar', 'icon' => '&#9636;'],
                ['label' => 'Total Stok', 'value' => Alats::sum('stok'), 'note' => 'Unit tersedia di inventaris', 'icon' => '&#9632;'],
                ['label' => 'Total Pengguna', 'value' => User::count(), 'note' => 'Akun terdaftar', 'icon' => '&#9823;'],
                ['label' => 'Total Peminjaman', 'value' => Peminjamans::count(), 'note' => 'Seluruh transaksi', 'icon' => '&#8599;'],
            ],
            'loanStatuses' => [
                'menunggu' => Peminjamans::where('status_peminjaman', 'menunggu')->count(),
                'disetujui' => (clone $approved)->count(),
                'ditolak' => Peminjamans::where('status_peminjaman', 'ditolak')->count(),
                'selesai' => $completed->count(),
            ],
            'categories' => Kategoris::query()->withSum('alats', 'stok')->orderBy('nama_kategori')->get(),
            'activities' => LogAktivitas::with('user')->latest()->take(8)->get(),
            'lowStock' => Alats::with('kategori')->where('stok', '<=', self::LOW_STOCK_THRESHOLD)->orderBy('stok')->orderBy('nama_alat')->take(8)->get(),
            'lowStockThreshold' => self::LOW_STOCK_THRESHOLD,
        ]);
    }

    private function petugasDashboard()
    {
        $approvedDetails = DetailPeminjamans::where('status', false)->whereHas('peminjaman', fn ($query) => $query->where('status_peminjaman', 'disetujui'));
        $overdue = (clone $approvedDetails)->whereHas('peminjaman', fn ($query) => $query->whereNotNull('tanggal_kembali_rencana')->whereDate('tanggal_kembali_rencana', '<', today()));
        return view('dashboard.index', [
            'role' => 'petugas',
            'stats' => [
                ['label' => 'Pengajuan Menunggu', 'value' => Peminjamans::where('status_peminjaman', 'menunggu')->count(), 'note' => 'Perlu ditinjau', 'icon' => '&#9719;'],
                ['label' => 'Peminjaman Disetujui', 'value' => Peminjamans::where('status_peminjaman', 'disetujui')->count(), 'note' => 'Transaksi disetujui', 'icon' => '&#10003;'],
                ['label' => 'Alat Sedang Dipinjam', 'value' => (clone $approvedDetails)->sum('jumlah'), 'note' => 'Unit belum kembali', 'icon' => '&#8599;'],
                ['label' => 'Pengembalian Terlambat', 'value' => $overdue->count(), 'note' => 'Transaksi melewati rencana', 'icon' => '!'],
            ],
            'pendingLoans' => Peminjamans::with(['user', 'details.alat'])->withCount('details')->where('status_peminjaman', 'menunggu')->latest()->take(6)->get(),
            'overdueItems' => (clone $overdue)->with(['alat', 'peminjaman.user'])->latest()->take(8)->get(),
            'activities' => LogAktivitas::with('user')->where(fn ($query) => $query->where('aktivitas', 'like', 'Peminjaman%')->orWhere('aktivitas', 'like', 'Pengembalian%'))->latest()->take(8)->get(),
        ]);
    }

    private function peminjamDashboard(int $userId)
    {
        $loans = Peminjamans::where('user_id', $userId);
        $approved = (clone $loans)->where('status_peminjaman', 'disetujui');
        $activeDetails = DetailPeminjamans::where('status', false)->whereHas('peminjaman', fn ($query) => $query->where('user_id', $userId)->where('status_peminjaman', 'disetujui'));
        $returnedLoans = (clone $approved)->whereHas('details')->whereDoesntHave('details', fn ($query) => $query->where('status', false));
        return view('dashboard.index', [
            'role' => 'peminjam',
            'stats' => [
                ['label' => 'Total Peminjaman Saya', 'value' => (clone $loans)->count(), 'note' => 'Seluruh pengajuan saya', 'icon' => '&#9635;'],
                ['label' => 'Menunggu Persetujuan', 'value' => (clone $loans)->where('status_peminjaman', 'menunggu')->count(), 'note' => 'Sedang ditinjau petugas', 'icon' => '&#10003;'],
                ['label' => 'Sedang Dipinjam', 'value' => (clone $activeDetails)->sum('jumlah'), 'note' => 'Unit belum dikembalikan', 'icon' => '&#10003;'],
                ['label' => 'Sudah Dikembalikan', 'value' => $returnedLoans->count(), 'note' => 'Transaksi selesai', 'icon' => '&#10003;'],
            ],
            'recentLoans' => (clone $loans)->withCount(['details', 'details as outstanding_details_count' => fn ($query) => $query->where('status', false)])->latest()->take(6)->get(),
            'activeItems' => (clone $activeDetails)->with(['alat', 'peminjaman'])->latest()->take(8)->get(),
            'activities' => LogAktivitas::with('user')->where('user_id', $userId)->latest()->take(6)->get(),
        ]);
    }
}




