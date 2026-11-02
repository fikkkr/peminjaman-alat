<?php
namespace App\Http\Controllers;
use App\Models\Peminjamans; use App\Models\Alats; use App\Support\CatatLog; use Illuminate\Support\Facades\DB; use Illuminate\Validation\ValidationException;
class PersetujuanController extends Controller {
 public function index(){return view('persetujuan.index',['items'=>Peminjamans::with(['user','details.alat'])->where('status_peminjaman','menunggu')->latest()->paginate(15)]);}
 public function setujui(Peminjamans $peminjaman){DB::transaction(function()use($peminjaman){$p=Peminjamans::whereKey($peminjaman->id)->lockForUpdate()->with('details')->firstOrFail();abort_unless($p->status_peminjaman==='menunggu',403);foreach($p->details as $d){$a=Alats::lockForUpdate()->findOrFail($d->alat_id);if($a->stok<$d->jumlah)throw ValidationException::withMessages(['stok'=>"Stok {$a->nama_alat} tidak cukup untuk menyetujui."]);$a->decrement('stok',$d->jumlah);}$p->update(['status_peminjaman'=>'disetujui']);});CatatLog::buat('Peminjaman disetujui');return back()->with('sukses','Peminjaman disetujui. Stok sudah dikurangi.');}
 public function tolak(Peminjamans $peminjaman){abort_unless($peminjaman->status_peminjaman==='menunggu',403);$peminjaman->update(['status_peminjaman'=>'ditolak']);CatatLog::buat('Peminjaman ditolak');return back()->with('sukses','Pengajuan peminjaman ditolak.');}
}
