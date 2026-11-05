@extends('layouts.app')
@section('title', 'Laporan')
@push('head')
<style>
  .chart { display: flex; align-items: flex-end; gap: 6px; height: 220px; padding-top: 10px; }
  .bar { flex: 1; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; gap: 6px; min-width: 0; }
  .bar i { display: block; width: 100%; max-width: 38px; background: var(--acc); border-radius: 6px 6px 2px 2px; min-height: 2px; }
  .bar small { font-size: 11px; color: var(--dim); white-space: nowrap; } .bar.we i { background: #c98a3a; }
  .heat { display: grid; grid-template-columns: repeat(15, 1fr); gap: 4px; }
  .heat div { aspect-ratio: 1; border-radius: 6px; display: grid; place-items: center; font-size: 10.5px; font-weight: 800; color: var(--acc-ink); }
  .heat small { text-align: center; font-size: 10.5px; color: var(--dim); }
  .rank { display: grid; grid-template-columns: 22px 1fr auto; gap: 4px 10px; align-items: center; padding: 8px 0; border-bottom: 1px dashed var(--line); }
  .rank:last-child { border: 0; } .rank em { font-style: normal; color: var(--dim); font-weight: 800; } .rank .mtr { grid-column: 2 / 4; height: 5px; background: var(--s2); border-radius: 3px; overflow: hidden; } .rank .mtr i { display: block; height: 100%; background: var(--acc); }
  .split { display: flex; height: 12px; border-radius: 6px; overflow: hidden; margin: 6px 0 12px; } .split i { display: block; height: 100%; }
  .kv { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px dashed var(--line); } .kv:last-child { border: 0; }
  .dot { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 8px; }
</style>
@endpush
@section('content')
<div class="head"><div><h1>Laporan penjualan</h1><p>{{ $dari->translatedFormat('d M Y') }} – {{ $sampai->translatedFormat('d M Y') }} · {{ $hari }} hari</p></div>
  <form class="acts" method="get">
    @foreach (['Hari ini' => [0, 0], '7 hari' => [6, 0], '30 hari' => [29, 0]] as $l => [$a, $b])
      <a class="btn sm" href="{{ route('laporan', ['dari' => today()->subDays($a)->toDateString(), 'sampai' => today()->subDays($b)->toDateString()]) }}">{{ $l }}</a>
    @endforeach
    <input type="date" name="dari" value="{{ $dari->toDateString() }}" class="in" style="width:auto;margin:0;height:34px"><input type="date" name="sampai" value="{{ $sampai->toDateString() }}" class="in" style="width:auto;margin:0;height:34px">
    <button class="btn sm">Tampilkan</button>
    <a class="btn sm acc" href="{{ route('laporan.csv', ['dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()]) }}">{!! icon('download', 15) !!} CSV</a>
  </form></div>
@php($naik = fn ($a, $b) => $b ? round(($a - $b) / $b * 100) : null)
<div class="stats">
  <div class="stat hl"><small>Omzet</small><b class="num">{{ rp($ring->t) }}</b>@php($p = $naik($ring->t, $seb->t))<span>@if ($p !== null)<span class="{{ $p >= 0 ? 'up' : 'down' }}">{{ $p >= 0 ? '+' : '' }}{{ $p }}%</span> dari periode sebelumnya @else periode sebelumnya kosong @endif</span></div>
  <div class="stat"><small>Transaksi</small><b>{{ number_format($ring->n, 0, ',', '.') }}</b>@php($p = $naik($ring->n, $seb->n))<span>@if ($p !== null)<span class="{{ $p >= 0 ? 'up' : 'down' }}">{{ $p >= 0 ? '+' : '' }}{{ $p }}%</span> · @endif{{ $batal }} dibatalkan</span></div>
  <div class="stat"><small>Rata-rata per transaksi</small><b class="num">{{ rp($ring->n ? $ring->t / $ring->n : 0) }}</b><span>{{ number_format($item, 0, ',', '.') }} item terjual</span></div>
  <div class="stat"><small>Waktu penyajian</small><b>{{ $prep ?: '-' }} <span style="font-size:15px">menit</span></b><span>rata-rata pesanan masuk sampai siap</span></div>
</div>
<div class="grid3">
  <div class="card pad"><h2>Omzet harian <small>hari libur akhir pekan berwarna lebih gelap</small></h2>
    @php($mx = max(1, max($grafik)))
    <div class="chart">@foreach ($grafik as $d => $v) @php($c = \Illuminate\Support\Carbon::parse($d))
      <div class="bar {{ $c->isWeekend() ? 'we' : '' }}" title="{{ $c->translatedFormat('D, d M') }}: {{ rp($v) }}"><i style="height:{{ round($v / $mx * 100) }}%"></i>@if (count($grafik) <= 16 || $loop->index % 3 === 0)<small>{{ $c->format('d/m') }}</small>@endif</div>
    @endforeach</div></div>
  <div class="card pad"><h2>Menu terlaris</h2>
    @php($tm = max(1, (int) ($terlaris->max('q') ?? 1)))
    @forelse ($terlaris as $i => $t)<div class="rank"><em>{{ $i + 1 }}</em><b>{{ $t->nama }}</b><span class="num">{{ $t->q }}</span><div class="mtr"><i style="width:{{ round($t->q / $tm * 100) }}%"></i></div></div>
    @empty<p class="empty">Belum ada penjualan.</p>@endforelse</div>
</div>
<div class="grid3" style="margin-top:16px">
  <div class="card pad"><h2>Jam ramai <small>jumlah pesanan per jam</small></h2>
    @php($jm = max(1, (int) ($jam->max() ?? 1)))
    <div class="heat">@for ($h = 7; $h <= 21; $h++) @php($n = (int) ($jam[$h] ?? 0))<div style="background:rgba(233,166,76,{{ max(.08, $n / $jm) }})" title="{{ $h }}.00: {{ $n }} pesanan">{{ $n ?: '' }}</div>@endfor
      @for ($h = 7; $h <= 21; $h++)<small>{{ $h }}</small>@endfor</div>
    <p class="mut sm" style="margin:14px 0 0">Gunakan untuk mengatur jadwal barista: tambah orang di jam yang paling gelap.</p></div>
  <div class="card pad"><h2>Pembayaran</h2>
    @php($mt = max(1, (int) $metode->sum('t'))) @php($warna = ['tunai' => '#e9a64c', 'qris' => '#7fb2e5', 'debit' => '#74c483'])
    <div class="split">@foreach ($metode as $m)<i style="width:{{ $m->t / $mt * 100 }}%;background:{{ $warna[$m->metode] ?? '#888' }}"></i>@endforeach</div>
    @foreach ($metode as $m)<div class="kv"><span><span class="dot" style="background:{{ $warna[$m->metode] ?? '#888' }}"></span>{{ \App\Models\Order::METODE[$m->metode] ?? $m->metode }} <span class="mut">· {{ $m->n }}x</span></span><b class="num">{{ rp($m->t) }}</b></div>@endforeach
    @php($tt = max(1, $tipe->sum()))
    <div class="kv" style="margin-top:8px"><span class="mut">Dine in / Takeaway</span><b>{{ round(($tipe['dine_in'] ?? 0) / $tt * 100) }}% / {{ round(($tipe['takeaway'] ?? 0) / $tt * 100) }}%</b></div></div>
</div>
<div class="card" style="margin-top:16px"><div style="padding:18px 20px 0"><h2>Shift terakhir</h2></div>
  <table class="tbl"><thead><tr><th>Kasir</th><th>Waktu</th><th class="r">Penjualan</th><th class="r">Selisih kas</th></tr></thead><tbody>
  @forelse ($shift as $s) @php($x = $s->ringkasan())
    <tr><td><b>{{ $s->user->name }}</b></td><td>{{ $s->dibuka_at->translatedFormat('D, d M') }} <span class="mut">{{ $s->dibuka_at->format('H.i') }}–{{ $s->ditutup_at->format('H.i') }}</span></td><td class="r num">{{ rp($x['total']) }}</td>
      <td class="r">@if ($x['selisih'] === 0)<span class="pill green">Pas</span>@else<span class="pill red">{{ $x['selisih'] > 0 ? '+' : '-' }}{{ rp(abs($x['selisih'])) }}</span>@endif</td></tr>
  @empty<tr><td colspan="4" class="empty">Belum ada shift di periode ini.</td></tr>@endforelse
  </tbody></table></div>
@endsection
