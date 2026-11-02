<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DetailPeminjamans extends Model { protected $fillable=['peminjaman_id','alat_id','jumlah','status']; protected $casts=['status'=>'boolean']; public function peminjaman(){return $this->belongsTo(Peminjamans::class,'peminjaman_id');} public function alat(){return $this->belongsTo(Alats::class,'alat_id');} public function pengembalian(){return $this->hasOne(Pengembalians::class,'detail_peminjaman_id');} }
