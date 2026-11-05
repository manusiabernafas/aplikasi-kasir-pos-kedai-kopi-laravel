<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Pengaturan;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KasirController extends Controller
{
    public function index(Request $r)
    {
        $shift = $r->user()->shiftAktif();
        if (!$shift) return redirect()->route('shift')->with('error', 'Buka shift terlebih dulu sebelum menerima pesanan.');
        $kategori = Kategori::orderBy('urut')->get(['id', 'nama']);
        $menu = $this->menuData();
        return view('kasir.index', ['kategori' => $kategori, 'menu' => $menu, 'shift' => $shift, 'pajak' => (float) setting('pajak_persen'), 'meja' => (int) setting('jumlah_meja')]);
    }

    public function menu()
    {
        return response()->json($this->menuData());
    }

    private function menuData()
    {
        return Menu::with('opsiGrup.opsi')->join('kategori', 'kategori.id', '=', 'menu.kategori_id')
            ->orderBy('kategori.urut')->orderBy('menu.urut')->orderBy('menu.nama')->select('menu.*')->get()->map(fn ($m) => [
            'id' => $m->id, 'k' => $m->kategori_id, 'nama' => $m->nama, 'harga' => $m->harga, 'tersedia' => $m->tersedia, 'foto' => $m->fotoUrl(), 'desk' => $m->deskripsi,
            'grup' => $m->opsiGrup->map(fn ($g) => ['id' => $g->id, 'nama' => $g->nama, 'wajib' => $g->wajib, 'multi' => $g->multi,
                'opsi' => $g->opsi->map(fn ($o) => ['id' => $o->id, 'nama' => $o->nama, 'tambahan' => $o->tambahan])])->values(),
        ]);
    }

    /**
     * Simpan pesanan. Harga, opsi, diskon, pajak, dan kembalian SELALU dihitung ulang di server.
     */
    public function store(Request $r)
    {
        $d = $r->validate([
            'items' => 'required|array|min:1|max:50',
            'items.*.menu_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1|max:99',
            'items.*.opsi' => 'array',
            'items.*.opsi.*' => 'integer',
            'items.*.catatan' => 'nullable|string|max:120',
            'tipe' => 'required|in:dine_in,takeaway',
            'meja' => 'nullable|string|max:10',
            'pelanggan' => 'nullable|string|max:60',
            'diskon_tipe' => 'nullable|in:persen,nominal',
            'diskon' => 'nullable|integer|min:0',
            'open_bill' => 'boolean',
            'metode' => 'nullable|in:tunai,qris,debit',
            'dibayar' => 'nullable|integer|min:0',
        ]);
        $user = $r->user();
        $shift = $user->shiftAktif();
        if (!$shift) return response()->json(['message' => 'Shift belum dibuka.'], 422);
        if ($d['tipe'] === 'dine_in' && empty($d['meja'])) throw ValidationException::withMessages(['meja' => 'Pilih nomor meja untuk dine in.']);
        if (!empty($d['open_bill']) && $d['tipe'] !== 'dine_in') throw ValidationException::withMessages(['open_bill' => 'Bayar nanti hanya untuk dine in.']);

        $menu = Menu::with('opsiGrup.opsi')->whereIn('id', array_column($d['items'], 'menu_id'))->get()->keyBy('id');
        $rows = []; $subtotal = 0; $keDapur = false;
        foreach ($d['items'] as $it) {
            $m = $menu[$it['menu_id']] ?? null;
            if (!$m || !$m->tersedia) throw ValidationException::withMessages(['items' => ($m->nama ?? 'Menu') . ' sedang tidak tersedia.']);
            $dipilih = collect($it['opsi'] ?? [])->unique();
            $tambahan = 0; $label = [];
            foreach ($m->opsiGrup as $g) {
                $ada = $g->opsi->whereIn('id', $dipilih);
                if ($g->wajib && $ada->isEmpty()) throw ValidationException::withMessages(['items' => "Pilih {$g->nama} untuk {$m->nama}."]);
                if (!$g->multi && $ada->count() > 1) throw ValidationException::withMessages(['items' => "{$g->nama} untuk {$m->nama} hanya boleh satu."]);
                foreach ($ada as $o) { $tambahan += $o->tambahan; $label[] = $o->nama; }
            }
            // Opsi yang bukan milik menu ini ditolak
            $sah = $m->opsiGrup->flatMap->opsi->pluck('id');
            if ($dipilih->diff($sah)->isNotEmpty()) throw ValidationException::withMessages(['items' => "Opsi tidak valid untuk {$m->nama}."]);
            $harga = $m->harga + $tambahan;
            $subtotal += $harga * $it['qty'];
            $keDapur = $keDapur || $m->ke_dapur;
            $rows[] = ['menu_id' => $m->id, 'nama' => $m->nama, 'opsi' => $label ? implode(', ', $label) : null, 'harga' => $harga, 'qty' => $it['qty'], 'catatan' => $it['catatan'] ?? null];
        }

        $nilai = (int) ($d['diskon'] ?? 0);
        $diskon = ($d['diskon_tipe'] ?? 'nominal') === 'persen' ? (int) round($subtotal * min(100, $nilai) / 100) : min($subtotal, $nilai);
        $pajak = (int) round(($subtotal - $diskon) * (float) setting('pajak_persen') / 100);
        $total = $subtotal - $diskon + $pajak;

        $bayarNanti = !empty($d['open_bill']);
        $dibayar = 0;
        if (!$bayarNanti && empty($d['metode'])) throw ValidationException::withMessages(['metode' => 'Pilih metode pembayaran.']);
        if (!$bayarNanti) {
            $dibayar = $d['metode'] === 'tunai' ? (int) ($d['dibayar'] ?? 0) : $total;
            if ($dibayar < $total) throw ValidationException::withMessages(['dibayar' => 'Uang yang diterima kurang dari total ' . rp($total) . '.']);
        }

        $order = $this->simpanDenganNomor(fn ($nomor) => Order::create([
            'tanggal' => today(), 'nomor' => $nomor, 'shift_id' => $shift->id, 'user_id' => $user->id, 'tipe' => $d['tipe'],
            'meja' => $d['tipe'] === 'dine_in' ? $d['meja'] : null, 'pelanggan' => ($d['pelanggan'] ?? null) ?: null,
            'subtotal' => $subtotal, 'diskon' => $diskon, 'pajak' => $pajak, 'total' => $total,
            'status_bayar' => $bayarNanti ? 'belum' : 'lunas', 'metode' => $bayarNanti ? null : $d['metode'],
            'dibayar' => $dibayar, 'kembalian' => $bayarNanti ? 0 : $dibayar - $total, 'dibayar_at' => $bayarNanti ? null : now(),
            'status_dapur' => $keDapur ? 'baru' : 'diambil',
        ]), $rows);

        return response()->json(['id' => $order->id, 'label' => $order->label(), 'total' => $total, 'kembalian' => $order->kembalian, 'struk' => route('struk', $order)]);
    }

    /** Nomor antrean harian berurutan; kunci baris agar dua kasir tidak mendapat nomor sama. */
    private function simpanDenganNomor(callable $buat, array $items): Order
    {
        for ($coba = 0; $coba < 3; $coba++) {
            try {
                return DB::transaction(function () use ($buat, $items) {
                    $nomor = (int) Order::where('tanggal', today())->lockForUpdate()->max('nomor') + 1;
                    $order = $buat($nomor);
                    $order->items()->createMany($items);
                    return $order;
                });
            } catch (QueryException $e) {
                if ($coba === 2 || ($e->errorInfo[1] ?? 0) !== 1062) throw $e;   // 1062 = nomor dobel, ulangi
            }
        }
        throw new \RuntimeException('Gagal membuat nomor antrean.');
    }

    public function openBills()
    {
        return Order::with('items')->where('status_bayar', 'belum')->orderBy('created_at')->get()->map(fn ($o) => [
            'id' => $o->id, 'label' => $o->label(), 'meja' => $o->meja, 'pelanggan' => $o->pelanggan, 'total' => $o->total, 'waktu' => $o->created_at->format('H.i'),
            'items' => $o->items->map(fn ($i) => $i->qty . 'x ' . $i->nama)->implode(', '),
        ]);
    }

    public function bayar(Request $r, Order $order)
    {
        $d = $r->validate(['metode' => 'required|in:tunai,qris,debit', 'dibayar' => 'nullable|integer|min:0']);
        $shift = $r->user()->shiftAktif();
        if (!$shift) return response()->json(['message' => 'Shift belum dibuka.'], 422);
        $hasil = DB::transaction(function () use ($order, $d, $shift) {
            $o = Order::lockForUpdate()->find($order->id);
            if ($o->status_bayar !== 'belum') throw ValidationException::withMessages(['order' => 'Pesanan ini sudah dibayar atau dibatalkan.']);
            $dibayar = $d['metode'] === 'tunai' ? (int) ($d['dibayar'] ?? 0) : $o->total;
            if ($dibayar < $o->total) throw ValidationException::withMessages(['dibayar' => 'Uang yang diterima kurang dari total ' . rp($o->total) . '.']);
            // Dicatat di shift yang menerima pembayaran
            $o->update(['status_bayar' => 'lunas', 'metode' => $d['metode'], 'dibayar' => $dibayar, 'kembalian' => $dibayar - $o->total, 'dibayar_at' => now(), 'shift_id' => $shift->id]);
            return $o;
        });
        return response()->json(['id' => $hasil->id, 'label' => $hasil->label(), 'total' => $hasil->total, 'kembalian' => $hasil->kembalian, 'struk' => route('struk', $hasil)]);
    }

    /** Batal: kasir hanya untuk pesanan yang belum mulai dibuat; owner boleh kapan saja di hari yang sama. */
    public function batal(Request $r, Order $order)
    {
        $d = $r->validate(['alasan' => 'required|string|min:3|max:200']);
        DB::transaction(function () use ($order, $d, $r) {
            $o = Order::lockForUpdate()->find($order->id);
            if ($o->status_bayar === 'batal') throw ValidationException::withMessages(['order' => 'Pesanan sudah dibatalkan.']);
            if (!$o->tanggal->isToday()) throw ValidationException::withMessages(['order' => 'Hanya pesanan hari ini yang bisa dibatalkan.']);
            if (!$r->user()->punya('owner') && $o->status_dapur !== 'baru') throw ValidationException::withMessages(['order' => 'Pesanan sudah diproses barista. Minta pemilik untuk membatalkan.']);
            $o->update(['status_bayar' => 'batal', 'alasan_batal' => $d['alasan'] . ' (' . $r->user()->name . ')', 'status_dapur' => 'diambil']);
        });
        return $r->expectsJson() ? response()->json(['ok' => true]) : back()->with('ok', 'Pesanan dibatalkan.');
    }

    public function struk(Order $order)
    {
        return view('kasir.struk', ['o' => $order->load('items', 'user'), 's' => Pengaturan::semua()]);
    }
}
