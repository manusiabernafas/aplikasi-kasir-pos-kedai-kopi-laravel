<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Batasi halaman berdasarkan role: ->middleware('role:owner,kasir') */
class Role
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $u = $request->user();
        if (!$u || !$u->aktif) {
            auth()->logout();
            return redirect()->route('login');
        }
        if (!$u->punya(...$roles)) {
            if ($request->expectsJson()) return response()->json(['message' => 'Akses ditolak untuk role ' . $u->role . '.'], 403);
            return redirect()->route('home')->with('error', 'Halaman tersebut bukan untuk role ' . \App\Models\User::ROLES[$u->role] . '.');
        }
        return $next($request);
    }
}
