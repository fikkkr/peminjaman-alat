<?php
namespace App\Http\Controllers;
use App\Models\Peminjamans; use App\Models\Pengembalians; use Illuminate\Http\Request;
class LaporanController extends Controller {public function index(Request $r){$from=$r->input('dari',now()->startOfMonth()->toDateString());$to=$r->input('sampai',now()->endOfMonth()->toDateString());return view('laporan.index',['dari'=>$from,'sampai'=>$to,'peminjamans'=>Peminjamans::with(['user','details.alat'])->whereBetween('tanggal_pinjam',[$from,$to])->get(),'pengembalians'=>Pengembalians::with(['detail.alat','detail.peminjaman.user'])->whereBetween('created_at',[$from,$to.' 23:59:59'])->get()]);}}
