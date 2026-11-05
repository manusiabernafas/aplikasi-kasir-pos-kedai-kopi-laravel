<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DapurController extends Controller
{
    public function index()
    {
        return view('dapur.index', ['menu' => Menu::orderBy('nama')->get(['id', 'nama', 'tersedia'])]);
    }

    /** Antrean dapur hari ini (dipanggil berkala oleh layar barista) */
    public function data()
    {
        $orders = Order::with(['items.menu:id,ke_dapur'])
            ->where('tanggal', today())->where('status_bayar', '!=', 'batal')
            ->whereIn('status_dapur', ['baru', 'dibuat', 'siap'])
            ->orderBy('created_at')->get();
        return response()->json([
            'server' => now()->timestamp,
            'orders' => $orders->map(fn ($o) => [
                'id' => $o->id, 'label' => $o->label(), 'status' => $o->status_dapur, 'tipe' => $o->tipe, 'meja' => $o->meja,
                'pelanggan' => $o->pelanggan, 'belum_bayar' => $o->status_bayar === 'belum',
                'masuk' => $o->created_at->timestamp, 'siap' => $o->siap_at?->timestamp,
                'items' => $o->items->filter(fn ($i) => $i->menu?->ke_dapur ?? true)->map(fn ($i) => ['qty' => $i->qty, 'nama' => $i->nama, 'opsi' => $i->opsi, 'catatan' => $i->catatan])->values(),
            ]),
            'selesai_hari_ini' => Order::where('tanggal', today())->where('status_dapur', 'diambil')->where('status_bayar', '!=', 'batal')->whereNotNull('siap_at')->count(),
            'rata_menit' => (int) round((float) Order::where('tanggal', today())->whereNotNull('siap_at')->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, siap_at)) s')->value('s') / 60),
        ]);
    }

    /** Majukan / mundurkan status: baru -> dibuat -> siap -> diambil */
    public function status(Request $r, Order $order)
    {
        $arah = $r->validate(['arah' => 'required|in:maju,mundur'])['arah'];
        $urut = ['baru', 'dibuat', 'siap', 'diambil'];
        $o = DB::transaction(function () use ($order, $arah, $urut) {
            $o = Order::lockForUpdate()->find($order->id);
            if ($o->status_bayar === 'batal') abort(422, 'Pesanan dibatalkan.');
            $i = array_search($o->status_dapur, $urut, true);
            $j = $arah === 'maju' ? min(3, $i + 1) : max(0, $i - 1);
            if ($i === $j) return $o;
            $baru = $urut[$j];
            $o->status_dapur = $baru;
            if ($arah === 'maju') {
                if ($baru === 'dibuat') $o->mulai_at = now();
                if ($baru === 'siap') $o->siap_at = now();
                if ($baru === 'diambil') $o->diambil_at = now();
            } else {
                if ($baru === 'baru') $o->mulai_at = null;
                if ($baru === 'dibuat') $o->siap_at = null;
                if ($baru === 'siap') $o->diambil_at = null;
            }
            $o->save();
            return $o;
        });
        return response()->json(['id' => $o->id, 'status' => $o->status_dapur]);
    }

    /** Barista bisa menandai menu habis langsung dari layar dapur */
    public function tersedia(Menu $menu)
    {
        $menu->update(['tersedia' => !$menu->tersedia]);
        return response()->json(['id' => $menu->id, 'tersedia' => $menu->tersedia]);
    }
}
