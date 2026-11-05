@extends('layouts.app')
@section('title', 'Menu')
@push('head')
<style>.mrow{display:flex;align-items:center;gap:12px}.mrow .ph{width:42px;height:42px;border-radius:10px;background:var(--s3);display:grid;place-items:center;font-weight:800;color:var(--acc);overflow:hidden;flex-shrink:0}.mrow .ph img{width:100%;height:100%;object-fit:cover}.cat-h td{background:var(--s2);font-weight:800;font-size:13px}.gl{display:flex;flex-wrap:wrap;gap:6px}.grp{padding:12px 0;border-bottom:1px dashed var(--line)}.grp:last-child{border:0}</style>
@endpush
@section('content')
<div class="head"><div><h1>Menu</h1><p>{{ $kategori->sum(fn ($k) => $k->menu->count()) }} menu dalam {{ $kategori->count() }} kategori</p></div>
  <div class="acts"><button class="btn acc" onclick="document.getElementById('dMenu').showModal()">{!! icon('plus', 18) !!} Tambah menu</button></div></div>
<div class="grid3">
  <div class="card"><table class="tbl"><thead><tr><th>Menu</th><th>Opsi</th><th class="r">Harga</th><th>Status</th><th></th></tr></thead><tbody>
    @foreach ($kategori as $k)
      <tr class="cat-h"><td colspan="5">{{ $k->nama }} <span class="mut">· {{ $k->menu->count() }}</span></td></tr>
      @forelse ($k->menu as $m)
        <tr><td><div class="mrow"><span class="ph">@if ($m->foto)<img src="{{ $m->fotoUrl() }}" alt="">@else{{ mb_substr($m->nama, 0, 1) }}@endif</span><div><b>{{ $m->nama }}</b>@if ($m->deskripsi)<small>{{ $m->deskripsi }}</small>@endif</div></div></td>
          <td><div class="gl">@foreach ($m->opsiGrup as $g)<span class="pill">{{ $g->nama }}</span>@endforeach</div></td>
          <td class="r num"><b>{{ rp($m->harga) }}</b></td>
          <td>{!! $m->tersedia ? '<span class="pill green">Tersedia</span>' : '<span class="pill red">Habis</span>' !!}@if (!$m->ke_dapur) <span class="pill">tanpa dapur</span>@endif</td>
          <td class="r"><a class="btn sm" href="{{ route('menu', ['edit' => $m->id]) }}">{!! icon('edit', 15) !!}</a></td></tr>
      @empty<tr><td colspan="5" class="mut">Belum ada menu.</td></tr>@endforelse
    @endforeach
  </tbody></table></div>
  <div>
    <div class="card pad"><h2>Kategori</h2>
      @foreach ($kategori as $k)
        <form method="post" action="{{ route('menu.kategori') }}" style="display:flex;gap:6px;margin-bottom:8px">@csrf<input type="hidden" name="id" value="{{ $k->id }}">
          <input name="nama" value="{{ $k->nama }}" class="in" style="margin:0;height:38px"><button class="btn sm" name="aksi" value="ubah" title="Simpan">{!! icon('check', 15) !!}</button>
          <button class="btn sm danger" name="aksi" value="hapus" title="Hapus" onclick="return confirm('Hapus kategori {{ $k->nama }}?')">{!! icon('trash', 15) !!}</button></form>
      @endforeach
      <form method="post" action="{{ route('menu.kategori') }}" style="display:flex;gap:6px;margin-top:14px">@csrf<input type="hidden" name="aksi" value="tambah"><input name="nama" placeholder="Kategori baru" class="in" style="margin:0;height:38px" required><button class="btn sm acc">{!! icon('plus', 15) !!}</button></form>
    </div>
    <div class="card pad" style="margin-top:16px"><h2>Grup opsi <a class="btn sm" href="{{ route('menu', ['grup' => 'baru']) }}">{!! icon('plus', 15) !!}</a></h2>
      @foreach ($grup as $g)<div class="grp"><div style="display:flex;justify-content:space-between;gap:8px"><b>{{ $g->nama }}</b><a class="btn sm" href="{{ route('menu', ['grup' => $g->id]) }}">{!! icon('edit', 15) !!}</a></div>
        <div class="mut sm">{{ $g->wajib ? 'Wajib' : 'Opsional' }} · {{ $g->multi ? 'boleh lebih dari satu' : 'pilih satu' }}</div>
        <div class="gl" style="margin-top:6px">@foreach ($g->opsi as $o)<span class="pill">{{ $o->nama }}{{ $o->tambahan ? ' +' . number_format($o->tambahan / 1000, 0) . 'rb' : '' }}</span>@endforeach</div></div>@endforeach
    </div>
  </div>
</div>

<dialog id="dMenu"><form method="post" action="{{ route('menu.simpan') }}" enctype="multipart/form-data">@csrf
  @if ($edit)<input type="hidden" name="id" value="{{ $edit->id }}">@endif
  <div class="dlg-h"><h3>{{ $edit ? 'Edit menu' : 'Tambah menu' }}</h3><a class="x" href="{{ route('menu') }}">{!! icon('x', 18) !!}</a></div>
  <div class="dlg-b">
    <label class="f">Nama<input name="nama" value="{{ old('nama', $edit->nama ?? '') }}" required maxlength="80"></label>
    <div class="grid2" style="gap:12px"><label class="f">Kategori<select name="kategori_id">@foreach ($kategori as $k)<option value="{{ $k->id }}" @selected(($edit->kategori_id ?? null) == $k->id)>{{ $k->nama }}</option>@endforeach</select></label>
      <label class="f">Harga<input type="number" name="harga" min="0" step="500" value="{{ old('harga', $edit->harga ?? '') }}" required></label></div>
    <label class="f">Deskripsi<input name="deskripsi" value="{{ old('deskripsi', $edit->deskripsi ?? '') }}" maxlength="200"></label>
    <label class="f">Foto (opsional, maks 2 MB)<input type="file" name="foto" accept="image/*" style="padding-top:10px"></label>
    <p class="f" style="margin-bottom:6px">Grup opsi</p>
    <div style="margin-bottom:14px">@foreach ($grup as $g)<label class="chk"><input type="checkbox" name="grup[]" value="{{ $g->id }}" @checked($edit && $edit->opsiGrup->contains($g->id))> {{ $g->nama }}</label>@endforeach</div>
    <input type="hidden" name="tersedia" value="0"><label class="chk"><input type="checkbox" name="tersedia" value="1" @checked($edit->tersedia ?? true)> Tersedia</label>
    <input type="hidden" name="ke_dapur" value="0"><label class="chk"><input type="checkbox" name="ke_dapur" value="1" @checked($edit->ke_dapur ?? true)> Tampil di layar barista</label>
  </div>
  <div class="dlg-f">@if ($edit)<button class="btn danger" form="fHapus" onclick="return confirm('Hapus menu ini? Riwayat transaksi tetap tersimpan.')">{!! icon('trash', 16) !!}</button>@endif<button class="btn acc block">Simpan</button></div>
</form>@if ($edit)<form id="fHapus" method="post" action="{{ route('menu.hapus', $edit) }}">@csrf @method('DELETE')</form>@endif</dialog>

<dialog id="dGrup"><form method="post" action="{{ route('menu.opsi') }}">@csrf
  @if ($editGrup)<input type="hidden" name="id" value="{{ $editGrup->id }}">@endif
  <div class="dlg-h"><h3>{{ $editGrup ? 'Edit grup opsi' : 'Grup opsi baru' }}</h3><a class="x" href="{{ route('menu') }}">{!! icon('x', 18) !!}</a></div>
  <div class="dlg-b"><label class="f">Nama grup<input name="nama" value="{{ $editGrup->nama ?? '' }}" placeholder="Ukuran" required maxlength="50"></label>
    <label class="f">Pilihan (satu per baris, format <code>Nama|tambahan harga</code>)<textarea name="pilihan" rows="5" required placeholder="Reguler|0&#10;Large|5000">{{ $editGrup ? $editGrup->opsi->map(fn ($o) => $o->nama . '|' . $o->tambahan)->implode("\n") : '' }}</textarea></label>
    <input type="hidden" name="wajib" value="0"><label class="chk"><input type="checkbox" name="wajib" value="1" @checked($editGrup->wajib ?? true)> Wajib dipilih</label>
    <input type="hidden" name="multi" value="0"><label class="chk"><input type="checkbox" name="multi" value="1" @checked($editGrup->multi ?? false)> Boleh pilih lebih dari satu</label></div>
  <div class="dlg-f">@if ($editGrup)<button class="btn danger" name="hapus" value="1" onclick="return confirm('Hapus grup opsi ini dari semua menu?')">{!! icon('trash', 16) !!}</button>@endif<button class="btn acc block">Simpan</button></div>
</form></dialog>
@push('scripts')<script>
  @if ($edit || $errors->any()) document.getElementById('dMenu').showModal(); @endif
  @if (request('grup')) document.getElementById('dGrup').showModal(); @endif
</script>@endpush
@endsection
