<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Support\CatatLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller {
 public function showLogin(){return view('auth.login');}
 public function showRegister(){return view('auth.register');}
 public function register(Request $request){$data=$request->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email','password'=>'required|string|min:8|confirmed']);$user=User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>$data['password'],'role'=>'peminjam']);Auth::login($user);$request->session()->regenerate();CatatLog::buat('Registrasi akun');return redirect()->route('dashboard')->with('sukses','Registrasi berhasil. Selamat datang di SIMPA.');}
 public function login(Request $request){$data=$request->validate(['email'=>'required|email','password'=>'required|string']); if(Auth::attempt($data)){$request->session()->regenerate(); CatatLog::buat('Login'); return redirect()->intended(route('dashboard'));} return back()->withErrors(['email'=>'Email atau password salah.'])->onlyInput('email');}
 public function logout(Request $request){CatatLog::buat('Logout'); Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login');}
}