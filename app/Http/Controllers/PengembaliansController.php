<?php

namespace App\Http\Controllers;

use App\Models\Pengembalians;
use Illuminate\Http\Request;

class PengembaliansController extends Controller
{
    public function index() { return view('pengembalians.index', ['pengembalians' => Pengembalians::query()->latest()->paginate(15)]); }
    public function create() { return view('pengembalians.form'); }
    public function store(Request $request) { Pengembalians::create($request->validate(array (
  'peminjaman_id' => 'required|integer',
  'catatan' => 'nullable|string',
))); return redirect()->route('pengembalians.index'); }
    public function show(Pengembalians $pengembalian) { return redirect()->route('pengembalians.edit', $pengembalian); }
    public function edit(Pengembalians $pengembalian) { return view('pengembalians.form', compact('pengembalian')); }
    public function update(Request $request, Pengembalians $pengembalian) { $pengembalian->update($request->validate(array (
  'peminjaman_id' => 'required|integer',
  'catatan' => 'nullable|string',
))); return redirect()->route('pengembalians.index'); }
    public function destroy(Pengembalians $pengembalian) { $pengembalian->delete(); return redirect()->route('pengembalians.index'); }
}