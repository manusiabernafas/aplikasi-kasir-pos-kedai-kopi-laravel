@extends('layouts.app')
@section('title', 'Shift')
@section('content')
<div class="head"><div><h1>Shift kasir</h1><p>Hitung uang laci di awal dan akhir shift agar selisih kas langsung ketahuan.</p></div>
  @if (session('cetak'))<a class="btn" href="{{ session('cetak') }}" target="_blank" rel="noopener">{!! icon('printer', 18) !!} Cetak rekap shift</a>@endif</div>

@if (!$aktif)
  <div class="grid2">
    <form method="post" action="{{ route('shift.buka') }}" class="card pad">@csrf
      <h2>Buka shift</h2>
      <p class="mut" style="margin-top:0">Masukkan jumlah uang tunai di laci kasir sebagai modal awal (uang kembalian).</p>
      <label class="f">Modal awal<input type="number" name="modal_awal" min="0" step="1000" value="300000" required class="num" style="font-size:20px;height:54px"></label>
      <button class="btn acc lg block">{!! icon('clock', 20) !!} Mulai shift</button>
    </form>
    <div class="card pad"><h2>Kenapa perlu shift?</h2><p class="mut" style="margin:0;line-height:1.7">Setiap transaksi dicatat ke shift kasir yang sedang bertugas. Saat tutup shift, sistem menghitung uang tunai yang seharusnya ada di laci (modal awal + penjualan tunai) lalu membandingkannya dengan uang yang benar-benar dihitung.</p></div>
  </div>
@else
  <div class="stats">
    <div class="stat"><small>Dibuka</small><b>{{ $aktif->dibuka_at->format('H.i') }}</b><span>{{ $aktif->dibuka_at->diffForHumans(null, true) }} yang lalu</span></div>
    <div class="stat"><small>Transaksi</small><b>{{ $ringkas['transaksi'] }}</b><span>total {{ rp($ringkas['total']) }}</span></div>
    <div class="stat"><small>Non tunai</small><b class="num">{{ rp($ringkas['qris'] + $ringkas['debit']) }}</b><span>QRIS {{ rp($ringkas['qris']) }} · Debit {{ rp($ringkas['debit']) }}</span></div>
    <div class="stat hl"><small>Uang tunai seharusnya</small><b class="num">{{ rp($ringkas['harus_ada']) }}</b><span>modal {{ rp($aktif->modal_awal) }} + tunai {{ rp($ringkas['tunai']) }}</span></div>
  </div>
  <form method="post" action="{{ route('shift.tutup') }}" class="card pad" style="max-width:560px" onsubmit="return confirm('Tutup shift sekarang?')">@csrf
    <h2>Tutup shift</h2>
    @if ($belumBayar)<div class="alert err">Masih ada {{ $belumBayar }} tagihan dine in yang belum dibayar. Tagihan tetap bisa dilunasi oleh shift berikutnya.</div>@endif
    <label class="f">Uang tunai yang dihitung di laci<input type="number" name="uang_fisik" id="fisik" min="0" step="500" required class="num" style="font-size:20px;height:54px"></label>
    <div class="alert" id="sel" style="display:none"></div>
    <label class="f">Catatan<input name="catatan" maxlength="200" placeholder="Opsional, misal alasan selisih"></label>
    <button class="btn acc lg block">{!! icon('check', 20) !!} Tutup shift</button>
  </form>
  @push('scripts')
  <script>
    document.getElementById('fisik').oninput = function () {
      var v = parseInt(this.value || '0', 10), s = v - {{ $ringkas['harus_ada'] }}, el = document.getElementById('sel');
      el.style.display = this.value ? '' : 'none'; el.className = 'alert' + (s === 0 ? '' : ' err');
      el.textContent = s === 0 ? 'Uang kas pas.' : 'Selisih ' + (s > 0 ? 'lebih ' : 'kurang ') + rp(Math.abs(s));
    };
  </script>
  @endpush
@endif

<div class="card" style="margin-top:18px"><div style="padding:18px 20px 0"><h2>Riwayat shift</h2></div>
  <table class="tbl"><thead><tr><th>Kasir</th><th>Waktu</th><th class="r">Penjualan</th><th class="r">Tunai seharusnya</th><th class="r">Dihitung</th><th class="r">Selisih</th><th></th></tr></thead><tbody>
  @forelse ($riwayat as $s) @php($x = $s->ringkasan())
    <tr><td><b>{{ $s->user->name }}</b></td><td>{{ $s->dibuka_at->translatedFormat('d M') }}<small>{{ $s->dibuka_at->format('H.i') }} - {{ $s->ditutup_at->format('H.i') }}</small></td>
      <td class="r num">{{ rp($x['total']) }}<small>{{ $x['transaksi'] }} transaksi</small></td><td class="r num">{{ rp($x['harus_ada']) }}</td><td class="r num">{{ rp($s->uang_fisik) }}</td>
      <td class="r">@if ($x['selisih'] === 0)<span class="pill green">Pas</span>@else<span class="pill red">{{ $x['selisih'] > 0 ? '+' : '-' }}{{ rp(abs($x['selisih'])) }}</span>@endif</td>
      <td class="r"><a class="btn sm" href="{{ route('shift.cetak', $s) }}" target="_blank" rel="noopener">{!! icon('printer', 15) !!}</a></td></tr>
  @empty <tr><td colspan="7" class="empty">Belum ada shift yang ditutup.</td></tr>
  @endforelse
  </tbody></table></div>
@endsection
