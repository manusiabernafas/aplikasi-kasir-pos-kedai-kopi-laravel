<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Menu;
use App\Models\Opsi;
use App\Models\OpsiGrup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index(Request $r)
    {
        return view('menu.index', [
            'kategori' => Kategori::with(['menu.opsiGrup'])->orderBy('urut')->get(),
            'grup' => OpsiGrup::with('opsi')->orderBy('urut')->get(),
            'edit' => $r->edit ? Menu::with('opsiGrup')->find($r->edit) : null,
            'editGrup' => $r->grup ? OpsiGrup::with('opsi')->find($r->grup) : null,
        ]);
    }

    public function simpan(Request $r)
    {
        $d = $r->validate([
            'id' => 'nullable|exists:menu,id', 'kategori_id' => 'required|exists:kategori,id', 'nama' => 'required|string|max:80',
            'deskripsi' => 'nullable|string|max:200', 'harga' => 'required|integer|min:0|max:100000000', 'foto' => 'nullable|image|max:2048',
            'grup' => 'array', 'grup.*' => 'exists:opsi_grup,id', 'tersedia' => 'boolean', 'ke_dapur' => 'boolean',
        ]);
        $m = isset($d['id']) ? Menu::find($d['id']) : new Menu();
        if ($r->hasFile('foto')) {
            if ($m->foto) Storage::disk('public')->delete($m->foto);
            $m->foto = $r->file('foto')->store('menu', 'public');
        }
        $m->fill(['kategori_id' => $d['kategori_id'], 'nama' => $d['nama'], 'deskripsi' => $d['deskripsi'] ?? null, 'harga' => $d['harga'],
            'tersedia' => $r->boolean('tersedia'), 'ke_dapur' => $r->boolean('ke_dapur')])->save();
        $m->opsiGrup()->sync($d['grup'] ?? []);
        return redirect()->route('menu')->with('ok', 'Menu "' . $m->nama . '" disimpan.');
    }

    public function hapus(Menu $menu)
    {
        // Riwayat transaksi tetap aman karena nama & harga sudah disalin ke order_items
        if ($menu->foto) Storage::disk('public')->delete($menu->foto);
        $menu->delete();
        return redirect()->route('menu')->with('ok', 'Menu dihapus.');
    }

    public function kategori(Request $r)
    {
        $d = $r->validate(['aksi' => 'required|in:tambah,ubah,hapus', 'id' => 'nullable|exists:kategori,id', 'nama' => 'required_unless:aksi,hapus|nullable|string|max:50']);
        if ($d['aksi'] === 'tambah') Kategori::create(['nama' => $d['nama'], 'urut' => (int) Kategori::max('urut') + 1]);
        if ($d['aksi'] === 'ubah') Kategori::whereKey($d['id'])->update(['nama' => $d['nama']]);
        if ($d['aksi'] === 'hapus') {
            if (Menu::where('kategori_id', $d['id'])->exists()) return back()->with('error', 'Kategori masih berisi menu. Pindahkan atau hapus menunya dulu.');
            Kategori::destroy($d['id']);
        }
        return redirect()->route('menu')->with('ok', 'Kategori diperbarui.');
    }

    /** Simpan grup opsi beserta pilihannya. Baris opsi: "Nama|tambahan harga" */
    public function opsi(Request $r)
    {
        $d = $r->validate(['id' => 'nullable|exists:opsi_grup,id', 'nama' => 'required|string|max:50', 'pilihan' => 'required|string', 'wajib' => 'boolean', 'multi' => 'boolean', 'hapus' => 'boolean']);
        if ($r->boolean('hapus') && !empty($d['id'])) {
            OpsiGrup::destroy($d['id']);
            return redirect()->route('menu')->with('ok', 'Grup opsi dihapus.');
        }
        DB::transaction(function () use ($d, $r) {
            $g = OpsiGrup::updateOrCreate(['id' => $d['id'] ?? null], ['nama' => $d['nama'], 'wajib' => $r->boolean('wajib'), 'multi' => $r->boolean('multi'), 'urut' => $d['id'] ?? ((int) OpsiGrup::max('urut') + 1)]);
            $g->opsi()->delete();
            foreach (array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $d['pilihan'])))) as $i => $baris) {
                [$nama, $tambah] = array_pad(array_map('trim', explode('|', $baris)), 2, '0');
                Opsi::create(['opsi_grup_id' => $g->id, 'nama' => mb_substr($nama, 0, 50), 'tambahan' => (int) preg_replace('/\D/', '', $tambah), 'urut' => $i]);
            }
        });
        return redirect()->route('menu')->with('ok', 'Grup opsi disimpan.');
    }
}
