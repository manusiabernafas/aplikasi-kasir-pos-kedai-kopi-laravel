<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index(Request $r)
    {
        $tgl = $r->date('tanggal') ?? today();
        $q = Order::with('user:id,name')->withCount('items')->whereDate('tanggal', $tgl)
            ->when($r->status, fn ($q, $s) => $q->where('status_bayar', $s))
            ->when($r->metode, fn ($q, $m) => $q->where('metode', $m))
            ->when($r->cari, fn ($q, $c) => $q->where(fn ($w) => $w->where('pelanggan', 'like', "%$c%")->orWhere('nomor', ltrim(preg_replace('/\D/', '', $c), '0') ?: -1)))
            ->orderByDesc('created_at');
        $ringkas = Order::whereDate('tanggal', $tgl)->where('status_bayar', 'lunas')->selectRaw('COUNT(*) n, COALESCE(SUM(total),0) t')->first();
        return view('riwayat.index', ['orders' => $q->paginate(25)->withQueryString(), 'tgl' => $tgl, 'ringkas' => $ringkas]);
    }

    public function show(Order $order)
    {
        return view('riwayat.show', ['o' => $order->load('items', 'user', 'shift.user')]);
    }
}
