<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Kategoris extends Model { protected $fillable=['nama_kategori','deskripsi']; public function alats(){return $this->hasMany(Alats::class,'kategori_id');} }
