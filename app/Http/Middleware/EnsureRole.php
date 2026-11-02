<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class EnsureRole {
 public function handle(Request $request, Closure $next, ...$roles) {
  if (!$request->user() || !in_array($request->user()->role, $roles, true)) abort(403, 'Anda tidak memiliki akses ke halaman ini.');
  return $next($request);
 }
}
