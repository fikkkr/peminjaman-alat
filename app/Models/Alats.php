<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Alats extends Model { protected $fillable=['kategori_id','nama_alat','stok']; public function kategori(){return $this->belongsTo(Kategoris::class,'kategori_id');} public function details(){return $this->hasMany(DetailPeminjamans::class,'alat_id');} }
