<?php
namespace App\Http\Controllers;
use App\Models\Alats;
class DaftarAlatController extends Controller { public function index(){return view('daftar-alat.index',['alats'=>Alats::with('kategori')->orderBy('nama_alat')->paginate(20)]);} }
