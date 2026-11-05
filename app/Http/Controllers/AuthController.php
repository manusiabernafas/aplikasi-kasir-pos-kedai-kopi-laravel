<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function form()
    {
        return view('auth.login');
    }

    public function login(Request $r)
    {
        $data = $r->validate(['username' => 'required|string', 'password' => 'required|string']);
        if (Auth::attempt($data + ['aktif' => true], true)) {
            $r->session()->regenerate();
            return redirect()->route('home');
        }
        return back()->withInput($r->only('username'))->with('error', 'Username atau password salah.');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('login');
    }

    /** Halaman awal sesuai role */
    public function home(Request $r)
    {
        return redirect()->route(match ($r->user()->role) { 'owner' => 'laporan', 'barista' => 'dapur', default => 'kasir' });
    }
}
