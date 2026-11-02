<?php

namespace App\Http\Controllers;

use App\Models\Peminjamans;
use Illuminate\Http\Request;

class PeminjamansController extends Controller
{
    public function index() { return view('peminjamans.index', ['peminjamen' => Peminjamans::query()->latest()->paginate(15)]); }
    public function create() { return view('peminjamans.form'); }
    public function store(Request $request) { Peminjamans::create($request->validate(array (
  'user_id' => 'required|integer',
  'tanggal_pinjam' => 'required|date',
  'tanggal_kembali_rencana' => 'nullable|date',
  'log_aktivitas_id' => 'nullable|integer',
))); return redirect()->route('peminjamans.index'); }
    public function show(Peminjamans $peminjaman) { return redirect()->route('peminjamans.edit', $peminjaman); }
    public function edit(Peminjamans $peminjaman) { return view('peminjamans.form', compact('peminjaman')); }
    public function update(Request $request, Peminjamans $peminjaman) { $peminjaman->update($request->validate(array (
  'user_id' => 'required|integer',
  'tanggal_pinjam' => 'required|date',
  'tanggal_kembali_rencana' => 'nullable|date',
  'log_aktivitas_id' => 'nullable|integer',
))); return redirect()->route('peminjamans.index'); }
    public function destroy(Peminjamans $peminjaman) { $peminjaman->delete(); return redirect()->route('peminjamans.index'); }
}