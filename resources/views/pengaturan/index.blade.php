@extends('layouts.app')
@section('title', 'Pengaturan')
@section('content')
<div class="head"><div><h1>Pengaturan</h1><p>Informasi kedai yang tampil di struk dan aturan transaksi</p></div></div>
<form method="post" action="{{ route('pengaturan.simpan') }}" class="card pad" style="max-width:720px">@csrf
  <div class="grid2" style="gap:0 16px">
    <label class="f">Nama kedai<input name="nama_toko" value="{{ $s['nama_toko'] }}" required maxlength="60"></label>
    <label class="f">Telepon<input name="telepon" value="{{ $s['telepon'] }}" maxlength="30"></label></div>
  <label class="f">Alamat<input name="alamat" value="{{ $s['alamat'] }}" maxlength="150"></label>
  <label class="f">Catatan kaki struk<input name="footer_struk" value="{{ $s['footer_struk'] }}" maxlength="150"></label>
  <div class="grid2" style="gap:0 16px">
    <label class="f">Pajak / PB1 (%)<input type="number" name="pajak_persen" step="0.5" min="0" max="30" value="{{ $s['pajak_persen'] }}"><span class="dim" style="font-weight:600">Isi 0 jika tidak memungut pajak</span></label>
    <label class="f">Jumlah meja<input type="number" name="jumlah_meja" min="0" max="200" value="{{ $s['jumlah_meja'] }}"></label></div>
  <label class="f">Kode layar TV<input name="kode_layar" value="{{ $s['kode_layar'] }}" maxlength="30" placeholder="Kosongkan agar layar bisa dibuka tanpa kode"><span class="dim" style="font-weight:600">Jika diisi, layar pesanan siap dibuka dengan {{ url('/layar') }}?kode=KODE</span></label>
  <button class="btn acc lg">{!! icon('check', 20) !!} Simpan pengaturan</button>
</form>
@endsection
