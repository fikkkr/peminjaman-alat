<?php
namespace App\Http\Controllers;
use App\Models\DetailPeminjamans;
class DetailPeminjamansController extends Controller { public function index(){ $q=DetailPeminjamans::with(['alat.kategori','peminjaman.user','pengembalian'])->latest(); if(auth()->user()->role==='peminjam')$q->whereHas('peminjaman',fn($x)=>$x->where('user_id',auth()->id()));return view('detail-peminjamans.index',['detailPeminjamen'=>$q->paginate(20)]); } }
