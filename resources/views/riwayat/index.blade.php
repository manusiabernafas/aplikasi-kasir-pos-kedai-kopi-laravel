@extends('layouts.app')
@section('title', 'Riwayat')
@section('content')
<div class="head"><div><h1>Riwayat transaksi</h1><p>{{ $tgl->translatedFormat('l, d F Y') }} · {{ $ringkas->n }} transaksi lunas · {{ rp($ringkas->t) }}</p></div>
  <form class="acts" method="get">
    <input type="date" name="tanggal" value="{{ $tgl->toDateString() }}" class="in" style="width:auto;margin:0" onchange="this.form.submit()">
    <select name="status" class="in" style="width:auto;margin:0" onchange="this.form.submit()"><option value="">Semua status</option>@foreach (['lunas' => 'Lunas', 'belum' => 'Belum bayar', 'batal' => 'Batal'] as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach</select>
    <select name="metode" class="in" style="width:auto;margin:0" onchange="this.form.submit()"><option value="">Semua metode</option>@foreach (\App\Models\Order::METODE as $k => $l)<option value="{{ $k }}" @selected(request('metode') === $k)>{{ $l }}</option>@endforeach</select>
    <input name="cari" value="{{ request('cari') }}" placeholder="Nama / nomor" class="in" style="width:170px;margin:0">
  </form></div>
<div class="card">
  <table class="tbl"><thead><tr><th>No.</th><th>Waktu</th><th>Pelanggan</th><th>Item</th><th>Dapur</th><th>Bayar</th><th class="r">Total</th><th></th></tr></thead><tbody>
  @forelse ($orders as $o)
    <tr><td><b>{{ $o->label() }}</b></td><td class="num">{{ $o->created_at->format('H.i') }}<small>{{ $o->user->name }}</small></td>
      <td>{{ $o->pelanggan ?: '-' }}<small>{{ $o->tipe === 'dine_in' ? 'Meja ' . $o->meja : 'Takeaway' }}</small></td><td>{{ $o->items_count }} item</td>
      <td><span class="pill {{ ['baru' => 'amber', 'dibuat' => 'blue', 'siap' => 'green', 'diambil' => ''][$o->status_dapur] }}">{{ \App\Models\Order::DAPUR[$o->status_dapur] }}</span></td>
      <td>@if ($o->status_bayar === 'lunas')<span class="pill green">{{ \App\Models\Order::METODE[$o->metode] }}</span>@elseif ($o->status_bayar === 'belum')<span class="pill amber">Belum bayar</span>@else<span class="pill red">Batal</span>@endif</td>
      <td class="r num"><b>{{ rp($o->total) }}</b></td><td class="r"><a class="btn sm" href="{{ route('riwayat.show', $o) }}">Detail</a></td></tr>
  @empty <tr><td colspan="8" class="empty">Tidak ada transaksi.</td></tr>
  @endforelse
  </tbody></table></div>
@if ($orders->hasPages())
  <div class="pager">@foreach ($orders->getUrlRange(1, $orders->lastPage()) as $p => $u)<a href="{{ $u }}" class="{{ $p === $orders->currentPage() ? 'on' : '' }}">{{ $p }}</a>@endforeach</div>
@endif
@endsection
