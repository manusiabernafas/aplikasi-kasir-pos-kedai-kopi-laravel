<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    private function periode(Request $r): array
    {
        $dari = $r->date('dari') ?? today()->subDays(6);
        $sampai = $r->date('sampai') ?? today();
        if ($dari->gt($sampai)) [$dari, $sampai] = [$sampai, $dari];
        return [$dari->startOfDay(), $sampai->endOfDay()];
    }

    public function index(Request $r)
    {
        [$dari, $sampai] = $this->periode($r);
        $lunas = fn () => Order::where('status_bayar', 'lunas')->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()]);
        $hari = (int) round($dari->copy()->startOfDay()->diffInDays($sampai->copy()->startOfDay())) + 1;
        // Periode sebelumnya dengan panjang sama, untuk perbandingan
        $sebDari = $dari->copy()->subDays((int) $hari); $sebSampai = $dari->copy()->subDay();
        $seb = Order::where('status_bayar', 'lunas')->whereBetween('tanggal', [$sebDari->toDateString(), $sebSampai->toDateString()])->selectRaw('COUNT(*) n, COALESCE(SUM(total),0) t')->first();

        $ring = $lunas()->selectRaw('COUNT(*) n, COALESCE(SUM(total),0) t, COALESCE(SUM(diskon),0) d')->first();
        $item = (int) $lunas()->join('order_items', 'order_items.order_id', '=', 'orders.id')->sum('order_items.qty');
        $harian = $lunas()->selectRaw('tanggal, COUNT(*) n, SUM(total) t')->groupBy('tanggal')->pluck('t', 'tanggal');
        $grafik = [];
        for ($d = $dari->copy(); $d->lte($sampai); $d->addDay()) $grafik[$d->toDateString()] = (int) ($harian[$d->toDateString()] ?? 0);

        $terlaris = $lunas()->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw('order_items.nama, SUM(order_items.qty) q, SUM(order_items.qty * order_items.harga) t')->groupBy('order_items.nama')->orderByDesc('q')->limit(8)->get();
        $metode = $lunas()->selectRaw('metode, COUNT(*) n, SUM(total) t')->groupBy('metode')->get();
        // Pola jam ramai: rata-rata pesanan per jam
        $jam = $lunas()->selectRaw('HOUR(created_at) j, COUNT(*) n')->groupBy('j')->pluck('n', 'j');
        $tipe = $lunas()->selectRaw('tipe, COUNT(*) n')->groupBy('tipe')->pluck('n', 'tipe');
        $batal = Order::where('status_bayar', 'batal')->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])->count();
        $shift = Shift::with('user')->whereBetween('dibuka_at', [$dari, $sampai])->whereNotNull('ditutup_at')->orderByDesc('dibuka_at')->limit(10)->get();
        $prep = (int) round((float) Order::whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])->whereNotNull('siap_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, siap_at)) s')->value('s') / 60);

        return view('laporan.index', compact('dari', 'sampai', 'ring', 'seb', 'item', 'grafik', 'terlaris', 'metode', 'jam', 'tipe', 'batal', 'shift', 'prep', 'hari'));
    }

    public function csv(Request $r)
    {
        [$dari, $sampai] = $this->periode($r);
        $rows = Order::with('items', 'user')->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])->orderBy('created_at')->get();
        return response()->streamDownload(function () use ($rows) {
            $o = fopen('php://output', 'w');
            fwrite($o, "\xEF\xBB\xBF");
            fputcsv($o, ['Tanggal', 'Jam', 'No', 'Tipe', 'Meja', 'Pelanggan', 'Item', 'Subtotal', 'Diskon', 'Pajak', 'Total', 'Status', 'Metode', 'Kasir'], ';', '"', '');
            foreach ($rows as $x) {
                fputcsv($o, [$x->tanggal->toDateString(), $x->created_at->format('H:i'), $x->label(), $x->tipe === 'dine_in' ? 'Dine in' : 'Takeaway', $x->meja, $x->pelanggan,
                    $x->items->map(fn ($i) => $i->qty . 'x ' . $i->nama . ($i->opsi ? " ($i->opsi)" : ''))->implode('; '),
                    $x->subtotal, $x->diskon, $x->pajak, $x->total, $x->status_bayar, $x->metode, $x->user->name], ';', '"', '');
            }
        }, 'penjualan-' . $dari->toDateString() . '-sd-' . $sampai->toDateString() . '.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
    }
}
