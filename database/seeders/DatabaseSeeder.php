<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Data contoh kedai kopi: menu, opsi, pengguna, dan riwayat transaksi 30 hari
 * (tanggal relatif terhadap hari ini, jadi dashboard & layar dapur selalu terisi).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(2026);
        $now = Carbon::now();
        $pw = Hash::make('admin123');
        foreach ([['Laras Ayuningtyas', 'owner', 'owner'], ['Rina Wulandari', 'kasir', 'kasir'], ['Dimas Prasetyo', 'dimas', 'kasir'], ['Yoga Pratama', 'barista', 'barista']] as [$n, $u, $r]) {
            DB::table('users')->insert(['name' => $n, 'username' => $u, 'role' => $r, 'aktif' => 1, 'password' => $pw, 'created_at' => $now, 'updated_at' => $now]);
        }
        Pengaturan::simpan(['nama_toko' => 'Kopi Lereng', 'alamat' => 'Jl. Kaliurang Km 7 No. 12, Sleman', 'telepon' => '0812-3456-7890',
            'footer_struk' => 'Terima kasih! Follow @kopilereng', 'pajak_persen' => '0', 'jumlah_meja' => '14']);

        // Grup opsi
        $grup = [];
        foreach ([
            'ukuran' => ['Ukuran', 1, 0, [['Reguler', 0], ['Large', 5000]]],
            'suhu' => ['Suhu', 1, 0, [['Dingin', 0], ['Panas', 0]]],
            'gula' => ['Gula', 1, 0, [['Normal', 0], ['Sedikit', 0], ['Tanpa gula', 0]]],
            'extra' => ['Tambahan', 0, 1, [['Extra shot', 6000], ['Oat milk', 8000], ['Whipped cream', 5000]]],
            'pedas' => ['Level pedas', 1, 0, [['Tidak pedas', 0], ['Sedang', 0], ['Pedas', 0]]],
        ] as $k => [$nama, $wajib, $multi, $opsi]) {
            $gid = DB::table('opsi_grup')->insertGetId(['nama' => $nama, 'wajib' => $wajib, 'multi' => $multi, 'urut' => count($grup), 'created_at' => $now, 'updated_at' => $now]);
            foreach ($opsi as $i => [$on, $tb]) DB::table('opsi')->insert(['opsi_grup_id' => $gid, 'nama' => $on, 'tambahan' => $tb, 'urut' => $i]);
            $grup[$k] = $gid;
        }

        $menu = [
            'Kopi' => [['Kopi Susu Gula Aren', 22000, 'ukuran suhu gula extra', 'Espresso, susu segar, gula aren cair'], ['Americano', 22000, 'ukuran suhu gula extra', null], ['Cafe Latte', 28000, 'ukuran suhu gula extra', null],
                ['Cappuccino', 28000, 'ukuran suhu extra', null], ['Caramel Macchiato', 32000, 'ukuran suhu extra', null], ['Espresso', 18000, '', 'Double shot'], ['Vietnam Drip', 22000, 'suhu', null],
                ['V60 Single Origin', 30000, 'suhu', 'Gayo / Toraja / Kintamani'], ['Kopi Tubruk', 15000, 'gula', null]],
            'Non Kopi' => [['Matcha Latte', 30000, 'ukuran suhu gula', null], ['Chocolate', 26000, 'ukuran suhu gula', null], ['Red Velvet', 26000, 'ukuran suhu gula', null], ['Taro Latte', 26000, 'ukuran suhu gula', null]],
            'Teh' => [['Es Teh Manis', 10000, 'gula', null], ['Lemon Tea', 15000, 'suhu gula', null], ['Lychee Tea', 20000, 'gula', null], ['Teh Tarik', 18000, 'suhu', null]],
            'Makanan' => [['Nasi Goreng Kampung', 28000, 'pedas', 'Telur ceplok, kerupuk, acar'], ['Mie Goreng Jawa', 26000, 'pedas', null], ['Chicken Katsu Rice', 32000, '', null], ['Roti Bakar Coklat Keju', 20000, '', null]],
            'Camilan' => [['Kentang Goreng', 18000, '', null], ['Pisang Goreng Keju', 16000, '', null], ['Croissant Butter', 22000, '', null], ['Cireng Rujak', 15000, 'pedas', null], ['Air Mineral', 6000, '', null]],
        ];
        $menuRows = [];
        $ki = 0;
        foreach ($menu as $kat => $items) {
            $kid = DB::table('kategori')->insertGetId(['nama' => $kat, 'urut' => $ki++, 'created_at' => $now, 'updated_at' => $now]);
            foreach ($items as $i => [$nama, $harga, $opsi, $desk]) {
                $mid = DB::table('menu')->insertGetId(['kategori_id' => $kid, 'nama' => $nama, 'deskripsi' => $desk, 'harga' => $harga, 'tersedia' => $nama === 'Taro Latte' ? 0 : 1,
                    'ke_dapur' => $nama === 'Air Mineral' ? 0 : 1, 'urut' => $i, 'created_at' => $now, 'updated_at' => $now]);
                foreach (array_filter(explode(' ', $opsi)) as $g) DB::table('menu_opsi_grup')->insert(['menu_id' => $mid, 'opsi_grup_id' => $grup[$g]]);
                $menuRows[] = ['id' => $mid, 'nama' => $nama, 'harga' => $harga, 'opsi' => array_filter(explode(' ', $opsi)), 'kat' => $kat, 'dapur' => $nama !== 'Air Mineral'];
            }
        }
        // Bobot popularitas menu (kopi susu paling laris)
        $bobot = array_map(fn ($m) => match ($m['nama']) { 'Kopi Susu Gula Aren' => 30, 'Americano' => 12, 'Es Teh Manis' => 14, 'Cafe Latte', 'Matcha Latte' => 10, 'Kentang Goreng', 'Nasi Goreng Kampung' => 9, 'Taro Latte' => 0, default => 4 }, $menuRows);
        $pick = function () use ($menuRows, $bobot) { $r = mt_rand(1, array_sum($bobot)); foreach ($bobot as $i => $b) { if (($r -= $b) <= 0) return $menuRows[$i]; } return $menuRows[0]; };
        $opsiPilih = ['ukuran' => [['Reguler', 0, 75], ['Large', 5000, 25]], 'suhu' => [['Dingin', 0, 80], ['Panas', 0, 20]], 'gula' => [['Normal', 0, 60], ['Sedikit', 0, 30], ['Tanpa gula', 0, 10]],
            'pedas' => [['Tidak pedas', 0, 30], ['Sedang', 0, 45], ['Pedas', 0, 25]], 'extra' => [['Extra shot', 6000, 6], ['Oat milk', 8000, 5], ['Whipped cream', 5000, 3]]];
        $acak = function (array $opsi) { $r = mt_rand(1, array_sum(array_column($opsi, 2))); foreach ($opsi as $o) { if (($r -= $o[2]) <= 0) return $o; } return $opsi[0]; };
        $nama = ['Rina', 'Dimas', 'Ayu', 'Bagus', 'Nadia', 'Fajar', 'Sari', 'Yoga', 'Intan', 'Hendra', 'Laras', 'Rizky', 'Maya', 'Arif', 'Tika', 'Galih', 'Wulan', 'Eko', 'Dewi', 'Andi', 'Putri', 'Bayu', 'Citra', 'Kevin'];
        // Distribusi jam ramai (07.00 - 21.59)
        $jam = [7 => 4, 8 => 9, 9 => 10, 10 => 7, 11 => 6, 12 => 8, 13 => 7, 14 => 6, 15 => 8, 16 => 10, 17 => 8, 18 => 7, 19 => 10, 20 => 11, 21 => 7];

        for ($h = 30; $h >= 0; $h--) {
            $tgl = $now->copy()->subDays($h)->startOfDay();
            $weekend = $tgl->isWeekend();
            $nOrder = mt_rand(55, 80) + ($weekend ? 30 : 0);
            $times = [];
            for ($i = 0; $i < $nOrder; $i++) {
                $r = mt_rand(1, array_sum($jam)); foreach ($jam as $j => $b) { if (($r -= $b) <= 0) break; }
                $t = $tgl->copy()->setTime($j, mt_rand(0, 59), mt_rand(0, 59));
                if ($t->lt($now)) $times[] = $t;
            }
            sort($times);
            if (!$times) continue;
            // Shift pagi (kasir) & sore (dimas)
            $shift = [];
            foreach ([[2, 7, 15], [3, 15, 22]] as [$uid, $a, $b]) {
                $buka = $tgl->copy()->setTime($a, 0)->subMinutes(mt_rand(5, 20));
                if ($buka->gt($now)) continue;
                $tutup = $tgl->copy()->setTime($b, mt_rand(0, 20));
                $shift[$a] = ['id' => DB::table('shifts')->insertGetId(['user_id' => $uid, 'dibuka_at' => $buka, 'modal_awal' => 300000, 'ditutup_at' => $tutup->lt($now) ? $tutup : null]), 'uid' => $uid, 'tutup' => $tutup];
            }
            foreach ($times as $no => $t) {
                $s = $t->hour < 15 ? ($shift[7] ?? null) : ($shift[15] ?? $shift[7] ?? null);
                if (!$s) continue;
                $items = []; $sub = 0;
                $n = [1, 1, 1, 2, 2, 2, 3, 4][mt_rand(0, 7)];
                for ($k = 0; $k < $n; $k++) {
                    $m = $pick(); $op = []; $tam = 0;
                    foreach ($m['opsi'] as $g) {
                        if ($g === 'extra') { if (mt_rand(1, 100) <= 14) { $o = $acak($opsiPilih['extra']); $op[] = $o[0]; $tam += $o[1]; } continue; }
                        $o = $acak($opsiPilih[$g]); $op[] = $o[0]; $tam += $o[1];
                    }
                    $qty = mt_rand(1, 10) <= 8 ? 1 : 2;
                    $items[] = ['menu_id' => $m['id'], 'nama' => $m['nama'], 'opsi' => $op ? implode(', ', $op) : null, 'harga' => $m['harga'] + $tam, 'qty' => $qty, 'catatan' => mt_rand(1, 25) === 1 ? 'Es dipisah' : null];
                    $sub += ($m['harga'] + $tam) * $qty;
                }
                $menit = $now->diffInMinutes($t, true);
                $tipe = mt_rand(1, 100) <= 60 ? 'dine_in' : 'takeaway';
                $batal = mt_rand(1, 1000) <= 12;
                $openBill = $h === 0 && $tipe === 'dine_in' && $menit < 50 && mt_rand(1, 100) <= 25;
                $metode = ['tunai', 'qris', 'qris', 'tunai', 'debit', 'qris', 'tunai', 'qris', 'tunai', 'debit'][mt_rand(0, 9)];
                $dibayar = $metode === 'tunai' ? (int) (ceil($sub / [5000, 10000, 50000, 100000][mt_rand(0, 3)]) * [5000, 10000, 50000, 100000][0]) : $sub;
                if ($metode === 'tunai') { $pecahan = [5000, 10000, 20000, 50000, 100000][mt_rand(0, 4)]; $dibayar = max($sub, (int) (ceil($sub / $pecahan) * $pecahan)); }
                // Status dapur sesuai umur pesanan
                $dapur = 'diambil';
                if ($h === 0) $dapur = $menit < 3 ? 'baru' : ($menit < 9 ? 'dibuat' : ($menit < 16 ? 'siap' : 'diambil'));
                $mulai = $t->copy()->addMinutes(mt_rand(1, 3)); $siap = $mulai->copy()->addMinutes(mt_rand(3, 9));
                $oid = DB::table('orders')->insertGetId([
                    'tanggal' => $tgl->toDateString(), 'nomor' => $no + 1, 'shift_id' => $s['id'], 'user_id' => $s['uid'], 'tipe' => $tipe,
                    'meja' => $tipe === 'dine_in' ? (string) mt_rand(1, 14) : null, 'pelanggan' => $nama[mt_rand(0, count($nama) - 1)],
                    'subtotal' => $sub, 'diskon' => 0, 'pajak' => 0, 'total' => $sub,
                    'status_bayar' => $batal ? 'batal' : ($openBill ? 'belum' : 'lunas'), 'metode' => $openBill || $batal ? null : $metode,
                    'dibayar' => $openBill || $batal ? 0 : $dibayar, 'kembalian' => $openBill || $batal ? 0 : $dibayar - $sub, 'dibayar_at' => $openBill || $batal ? null : $t,
                    'status_dapur' => $batal ? 'diambil' : $dapur, 'mulai_at' => in_array($dapur, ['dibuat', 'siap', 'diambil']) ? $mulai : null,
                    'siap_at' => in_array($dapur, ['siap', 'diambil']) ? $siap : null, 'diambil_at' => $dapur === 'diambil' ? $siap->copy()->addMinutes(mt_rand(1, 5)) : null,
                    'alasan_batal' => $batal ? 'Pelanggan membatalkan' : null, 'created_at' => $t, 'updated_at' => $t,
                ]);
                foreach ($items as $it) DB::table('order_items')->insert($it + ['order_id' => $oid]);
            }
            // Isi uang fisik saat tutup shift (sebagian besar pas, kadang selisih kecil)
            foreach ($shift as $sv) {
                $row = DB::table('shifts')->where('id', $sv['id'])->first();
                if (!$row->ditutup_at) continue;
                $tunai = (int) DB::table('orders')->where('shift_id', $sv['id'])->where('status_bayar', 'lunas')->where('metode', 'tunai')->sum('total');
                $selisih = [0, 0, 0, 0, 0, 0, -2000, 1000, -5000, 0][mt_rand(0, 9)];
                DB::table('shifts')->where('id', $sv['id'])->update(['uang_fisik' => 300000 + $tunai + $selisih, 'catatan' => $selisih < 0 ? 'Selisih kurang, sudah dicek ulang' : null]);
            }
        }
    }
}
