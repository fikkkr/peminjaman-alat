<?php
namespace App\Support;
use App\Models\LogAktivitas; use Illuminate\Support\Facades\Auth; use Throwable;
class CatatLog { public static function buat(string $aktivitas): void { try { if (Auth::check()) LogAktivitas::create(['user_id'=>Auth::id(),'aktivitas'=>$aktivitas]); } catch (Throwable $e) { report($e); } } }
