<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    public function index()
    {
        return view('pengaturan.index', ['s' => Pengaturan::semua()]);
    }

    public function simpan(Request $r)
    {
        $d = $r->validate([
            'nama_toko' => 'required|string|max:60', 'alamat' => 'nullable|string|max:150', 'telepon' => 'nullable|string|max:30',
            'footer_struk' => 'nullable|string|max:150', 'pajak_persen' => 'required|numeric|min:0|max:30', 'jumlah_meja' => 'required|integer|min:0|max:200',
            'kode_layar' => 'nullable|alpha_num|max:30',
        ]);
        Pengaturan::simpan(array_map(fn ($v) => (string) ($v ?? ''), $d));
        return back()->with('ok', 'Pengaturan disimpan.');
    }
}
