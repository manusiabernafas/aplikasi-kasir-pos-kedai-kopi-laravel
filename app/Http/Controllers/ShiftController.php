<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    public function index(Request $r)
    {
        $aktif = $r->user()->shiftAktif();
        $riwayat = Shift::with('user')->when(!$r->user()->punya('owner'), fn ($q) => $q->where('user_id', $r->user()->id))
            ->whereNotNull('ditutup_at')->orderByDesc('dibuka_at')->limit(20)->get();
        return view('shift.index', ['aktif' => $aktif, 'ringkas' => $aktif?->ringkasan(), 'riwayat' => $riwayat,
            'belumBayar' => $aktif ? \App\Models\Order::where('status_bayar', 'belum')->count() : 0]);
    }

    public function buka(Request $r)
    {
        $d = $r->validate(['modal_awal' => 'required|integer|min:0|max:100000000']);
        DB::transaction(function () use ($r, $d) {
            // Kunci user agar klik ganda tidak membuka dua shift
            DB::table('users')->where('id', $r->user()->id)->lockForUpdate()->first();
            if ($r->user()->shiftAktif()) return;
            Shift::create(['user_id' => $r->user()->id, 'dibuka_at' => now(), 'modal_awal' => $d['modal_awal']]);
        });
        return redirect()->route('kasir')->with('ok', 'Shift dibuka. Selamat bekerja!');
    }

    public function tutup(Request $r)
    {
        $d = $r->validate(['uang_fisik' => 'required|integer|min:0', 'catatan' => 'nullable|string|max:200']);
        $s = $r->user()->shiftAktif();
        if (!$s) return redirect()->route('shift')->with('error', 'Tidak ada shift yang terbuka.');
        $s->update(['ditutup_at' => now(), 'uang_fisik' => $d['uang_fisik'], 'catatan' => $d['catatan'] ?? null]);
        $sel = $s->ringkasan()['selisih'];
        return redirect()->route('shift')->with($sel === 0 ? 'ok' : 'error', 'Shift ditutup. ' . ($sel === 0 ? 'Uang kas pas.' : 'Selisih kas ' . ($sel > 0 ? '+' : '-') . rp(abs($sel)) . '.'))
            ->with('cetak', route('shift.cetak', $s));
    }

    public function cetak(Request $r, Shift $shift)
    {
        abort_unless($r->user()->punya('owner') || $shift->user_id === $r->user()->id, 403);
        $menu = $shift->orders()->where('status_bayar', 'lunas')->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw('order_items.nama, SUM(order_items.qty) q')->groupBy('order_items.nama')->orderByDesc('q')->get();
        return view('shift.cetak', ['s' => $shift->load('user'), 'r' => $shift->ringkasan(), 'menu' => $menu, 'set' => Pengaturan::semua(),
            'batal' => $shift->orders()->where('status_bayar', 'batal')->count()]);
    }
}
