<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Pengembalians extends Model { protected $fillable=['detail_peminjaman_id','catatan']; public function detail(){return $this->belongsTo(DetailPeminjamans::class,'detail_peminjaman_id');} }
