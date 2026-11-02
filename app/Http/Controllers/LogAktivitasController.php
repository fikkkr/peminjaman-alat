<?php
namespace App\Http\Controllers;
use App\Models\LogAktivitas;
class LogAktivitasController extends Controller { public function index(){return view('log-aktivitas.index',['logAktivitas'=>LogAktivitas::with('user')->latest()->paginate(20)]);} }
