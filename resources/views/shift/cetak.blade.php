<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Rekap shift</title>
<style>body{margin:0;background:#e9e5df;font:12px/1.5 "Courier New",monospace;color:#111}.s{width:58mm;margin:16px auto;background:#fff;padding:4mm 3mm 6mm}.c{text-align:center}.b{font-weight:bold}.hr{border-top:1px dashed #000;margin:6px 0}.row{display:flex;justify-content:space-between;gap:6px}@media print{body{background:#fff}.s{margin:0;width:auto}@page{size:58mm auto;margin:0}}</style></head>
<body onload="setTimeout(function(){window.print()},300)"><div class="s">
  <div class="c b" style="font-size:14px">{{ $set['nama_toko'] }}</div><div class="c">REKAP SHIFT</div><div class="hr"></div>
  <div class="row"><span>Kasir</span><span>{{ $s->user->name }}</span></div>
  <div class="row"><span>Buka</span><span>{{ $s->dibuka_at->format('d/m H:i') }}</span></div>
  <div class="row"><span>Tutup</span><span>{{ $s->ditutup_at?->format('d/m H:i') ?? '-' }}</span></div><div class="hr"></div>
  <div class="row"><span>Transaksi</span><span>{{ $r['transaksi'] }}</span></div>
  <div class="row"><span>Tunai</span><span>{{ number_format($r['tunai'], 0, ',', '.') }}</span></div>
  <div class="row"><span>QRIS</span><span>{{ number_format($r['qris'], 0, ',', '.') }}</span></div>
  <div class="row"><span>Debit</span><span>{{ number_format($r['debit'], 0, ',', '.') }}</span></div>
  <div class="row b"><span>TOTAL</span><span>{{ number_format($r['total'], 0, ',', '.') }}</span></div>
  <div class="row"><span>Dibatalkan</span><span>{{ $batal }}</span></div><div class="hr"></div>
  <div class="row"><span>Modal awal</span><span>{{ number_format($s->modal_awal, 0, ',', '.') }}</span></div>
  <div class="row"><span>Kas seharusnya</span><span>{{ number_format($r['harus_ada'], 0, ',', '.') }}</span></div>
  <div class="row"><span>Kas dihitung</span><span>{{ $s->uang_fisik === null ? '-' : number_format($s->uang_fisik, 0, ',', '.') }}</span></div>
  <div class="row b"><span>Selisih</span><span>{{ $r['selisih'] === null ? '-' : number_format($r['selisih'], 0, ',', '.') }}</span></div>
  @if ($s->catatan)<div>{{ $s->catatan }}</div>@endif<div class="hr"></div>
  <div class="b">Menu terjual</div>
  @foreach ($menu as $m)<div class="row"><span>{{ $m->nama }}</span><span>{{ $m->q }}</span></div>@endforeach
</div></body></html>
