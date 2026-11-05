@extends('layouts.app')
@section('title', 'Transaksi ' . $o->label())
@section('content')
<div class="head"><div><p style="margin:0 0 6px"><a href="{{ route('riwayat', ['tanggal' => $o->tanggal->toDateString()]) }}">{!! icon('back', 16) !!} Riwayat</a></p>
  <h1>{{ $o->label() }} · {{ rp($o->total) }}</h1><p>{{ $o->created_at->translatedFormat('l, d F Y H.i') }} · kasir {{ $o->user->name }}</p></div>
  <div class="acts"><a class="btn" href="{{ route('struk', $o) }}" target="_blank" rel="noopener">{!! icon('printer', 18) !!} Cetak struk</a>
  @if ($o->status_bayar !== 'batal' && $o->tanggal->isToday() && (auth()->user()->punya('owner') || $o->status_dapur === 'baru'))
    <button class="btn danger" onclick="document.getElementById('dBatal').showModal()">{!! icon('x', 18) !!} Batalkan</button>
  @endif</div></div>
<div class="grid3">
  <div class="card"><table class="tbl"><thead><tr><th>Item</th><th class="r">Qty</th><th class="r">Harga</th><th class="r">Jumlah</th></tr></thead><tbody>
    @foreach ($o->items as $i)<tr><td><b>{{ $i->nama }}</b>@if ($i->opsi)<small>{{ $i->opsi }}</small>@endif @if ($i->catatan)<small style="color:var(--amber)">{{ $i->catatan }}</small>@endif</td><td class="r">{{ $i->qty }}</td><td class="r num">{{ rp($i->harga) }}</td><td class="r num">{{ rp($i->harga * $i->qty) }}</td></tr>@endforeach
    <tr><td colspan="3" class="r mut">Subtotal</td><td class="r num">{{ rp($o->subtotal) }}</td></tr>
    @if ($o->diskon)<tr><td colspan="3" class="r mut">Diskon</td><td class="r num">-{{ rp($o->diskon) }}</td></tr>@endif
    @if ($o->pajak)<tr><td colspan="3" class="r mut">Pajak</td><td class="r num">{{ rp($o->pajak) }}</td></tr>@endif
    <tr><td colspan="3" class="r"><b>Total</b></td><td class="r num"><b>{{ rp($o->total) }}</b></td></tr>
  </tbody></table></div>
  <div class="card pad"><h2>Status</h2>
    <p>Pembayaran: @if ($o->status_bayar === 'lunas')<span class="pill green">Lunas · {{ \App\Models\Order::METODE[$o->metode] }}</span>@elseif ($o->status_bayar === 'belum')<span class="pill amber">Belum bayar</span>@else<span class="pill red">Dibatalkan</span>@endif</p>
    @if ($o->status_bayar === 'lunas')<p class="mut sm">Dibayar {{ rp($o->dibayar) }}, kembali {{ rp($o->kembalian) }} · {{ $o->dibayar_at->format('H.i') }}</p>@endif
    @if ($o->alasan_batal)<p class="mut sm">Alasan batal: {{ $o->alasan_batal }}</p>@endif
    <p>Dapur: <span class="pill">{{ \App\Models\Order::DAPUR[$o->status_dapur] }}</span></p>
    <p class="mut sm">Masuk {{ $o->created_at->format('H.i') }}@if ($o->mulai_at) · dibuat {{ $o->mulai_at->format('H.i') }}@endif @if ($o->siap_at) · siap {{ $o->siap_at->format('H.i') }} ({{ (int) $o->created_at->diffInMinutes($o->siap_at) }} menit)@endif</p>
    <p class="mut sm">{{ $o->tipe === 'dine_in' ? 'Dine in · Meja ' . $o->meja : 'Takeaway' }}{{ $o->pelanggan ? ' · ' . $o->pelanggan : '' }}</p></div>
</div>
<dialog id="dBatal"><form method="post" action="{{ route('kasir.batal', $o) }}">@csrf<div class="dlg-h"><h3>Batalkan {{ $o->label() }}?</h3></div>
  <div class="dlg-b"><label class="f">Alasan<input name="alasan" required minlength="3" maxlength="200" placeholder="Contoh: pelanggan membatalkan"></label></div>
  <div class="dlg-f"><button type="button" class="btn" onclick="this.closest('dialog').close()">Kembali</button><button class="btn acc block">Batalkan pesanan</button></div></form></dialog>
@endsection
