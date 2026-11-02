<?php
namespace Database\Seeders;
use App\Models\User; use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder { public function run():void { foreach([['admin@simpa.test','Admin SIMPA','admin'],['petugas@simpa.test','Petugas SIMPA','petugas'],['budi@simpa.test','Budi Santoso','peminjam']] as [$email,$name,$role]) User::updateOrCreate(['email'=>$email],['name'=>$name,'password'=>'password','role'=>$role]); } }
