<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Peminjamans extends Model { protected $fillable=['user_id','tanggal_pinjam','tanggal_kembali_rencana','log_aktivitas_id','status_peminjaman']; public function user(){return $this->belongsTo(User::class);} public function details(){return $this->hasMany(DetailPeminjamans::class,'peminjaman_id');} }
