<?php
namespace App\Http\Controllers;
use App\Support\CatatLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller {
 public function showLogin(){return view('auth.login');}
 public function login(Request $request){$data=$request->validate(['email'=>'required|email','password'=>'required|string']); if(Auth::attempt($data)){$request->session()->regenerate(); CatatLog::buat('Login'); return redirect()->intended(route('dashboard'));} return back()->withErrors(['email'=>'Email atau password salah.'])->onlyInput('email');}
 public function logout(Request $request){CatatLog::buat('Logout'); Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login');}
}
