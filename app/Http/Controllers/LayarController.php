<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class LayarController extends Controller
{
    private function izin(Request $r): void
    {
        // Jika "kode layar" diisi di Pengaturan, layar hanya bisa dibuka dengan ?kode=...
        $kode = setting('kode_layar');
        if ($kode !== '' && !hash_equals($kode, (string) $r->query('kode'))) abort(403, 'Kode layar salah.');
    }

    public function index(Request $r)
    {
        $this->izin($r);
        return view('layar.index', ['kode' => (string) $r->query('kode')]);
    }

    public function data(Request $r)
    {
        $this->izin($r);
        $q = fn ($s) => Order::where('tanggal', today())->where('status_bayar', '!=', 'batal')->where('status_dapur', $s);
        $nama = fn ($o) => $o->pelanggan ? explode(' ', $o->pelanggan)[0] : null;
        return response()->json([
            'siap' => $q('siap')->orderByDesc('siap_at')->limit(10)->get()->map(fn ($o) => ['id' => $o->id, 'label' => $o->label(), 'nama' => $nama($o)]),
            'proses' => $q('dibuat')->orderBy('created_at')->limit(12)->get()->merge($q('baru')->orderBy('created_at')->limit(12)->get())->map(fn ($o) => ['label' => $o->label(), 'nama' => $nama($o)])->values(),
        ]);
    }
}
