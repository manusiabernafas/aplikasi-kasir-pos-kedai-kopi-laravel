<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('pengguna.index', ['users' => User::orderByRaw("FIELD(role,'owner','kasir','barista')")->orderBy('name')->get()]);
    }

    public function simpan(Request $r)
    {
        $id = $r->integer('id') ?: null;
        $d = $r->validate([
            'name' => 'required|string|max:80',
            'username' => ['required', 'alpha_dash', 'max:50', Rule::unique('users')->ignore($id)],
            'role' => 'required|in:owner,kasir,barista',
            'password' => ($id ? 'nullable' : 'required') . '|string|min:6',
            'aktif' => 'boolean',
        ]);
        if ($id === $r->user()->id && ($d['role'] !== 'owner' || !$r->boolean('aktif'))) {
            return back()->with('error', 'Kamu tidak bisa menurunkan role atau menonaktifkan akunmu sendiri.');
        }
        $u = $id ? User::findOrFail($id) : new User();
        $u->fill(['name' => $d['name'], 'username' => $d['username'], 'role' => $d['role'], 'aktif' => $r->boolean('aktif')]);
        if (!empty($d['password'])) $u->password = $d['password'];
        $u->save();
        return redirect()->route('pengguna')->with('ok', 'Pengguna ' . $u->name . ' disimpan.');
    }
}
