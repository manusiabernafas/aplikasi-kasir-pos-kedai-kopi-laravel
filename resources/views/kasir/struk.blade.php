<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Struk {{ $o->label() }}</title>
<style>
  /* Lebar 58mm thermal; di layar ditampilkan di tengah */
  body { margin: 0; background: #e9e5df; font: 12px/1.45 "Courier New", ui-monospace, monospace; color: #111; }
  .s { width: 58mm; margin: 16px auto; background: #fff; padding: 4mm 3mm 6mm; }
  .c { text-align: center; } .b { font-weight: bold; } .big { font-size: 22px; font-weight: bold; letter-spacing: 1px; }
  .hr { border-top: 1px dashed #000; margin: 6px 0; } .row { display: flex; justify-content: space-between; gap: 6px; }
  .it { margin: 3px 0; } .op { font-size: 11px; padding-left: 8px; } .tools { text-align: center; margin: 10px 0 30px; font-family: system-ui, sans-serif; }
  .tools button { padding: 8px 16px; border-radius: 8px; border: 0; background: #2a1a06; color: #fff; cursor: pointer; font-weight: 700; }
  @media print { body { background: #fff; } .s { margin: 0; width: auto; } .tools { display: none; } @page { size: 58mm auto; margin: 0; } }
</style></head>
<body onload="if(location.hash!=='#noprint')setTimeout(function(){window.print()},300)">
<div class="s">
  <div class="c b" style="font-size:14px">{{ $s['nama_toko'] }}</div>
  @if ($s['alamat'])<div class="c">{{ $s['alamat'] }}</div>@endif
  @if ($s['telepon'])<div class="c">{{ $s['telepon'] }}</div>@endif
  <div class="hr"></div>
  <div class="c">No. antrean</div><div class="c big">{{ $o->label() }}</div>
  <div class="c">{{ $o->tipe === 'dine_in' ? 'Dine in · Meja ' . $o->meja : 'Takeaway' }}{{ $o->pelanggan ? ' · ' . $o->pelanggan : '' }}</div>
  <div class="hr"></div>
  <div class="row"><span>{{ $o->created_at->format('d/m/Y H:i') }}</span><span>{{ $o->user->name }}</span></div>
  <div class="hr"></div>
  @foreach ($o->items as $i)
    <div class="it"><div>{{ $i->nama }}</div>
      @if ($i->opsi)<div class="op">{{ $i->opsi }}</div>@endif
      @if ($i->catatan)<div class="op">* {{ $i->catatan }}</div>@endif
      <div class="row"><span>&nbsp; {{ $i->qty }} x {{ number_format($i->harga, 0, ',', '.') }}</span><span>{{ number_format($i->qty * $i->harga, 0, ',', '.') }}</span></div></div>
  @endforeach
  <div class="hr"></div>
  <div class="row"><span>Subtotal</span><span>{{ number_format($o->subtotal, 0, ',', '.') }}</span></div>
  @if ($o->diskon)<div class="row"><span>Diskon</span><span>-{{ number_format($o->diskon, 0, ',', '.') }}</span></div>@endif
  @if ($o->pajak)<div class="row"><span>Pajak</span><span>{{ number_format($o->pajak, 0, ',', '.') }}</span></div>@endif
  <div class="row b" style="font-size:14px"><span>TOTAL</span><span>{{ number_format($o->total, 0, ',', '.') }}</span></div>
  @if ($o->status_bayar === 'lunas')
    <div class="row"><span>{{ \App\Models\Order::METODE[$o->metode] }}</span><span>{{ number_format($o->dibayar, 0, ',', '.') }}</span></div>
    @if ($o->kembalian)<div class="row"><span>Kembali</span><span>{{ number_format($o->kembalian, 0, ',', '.') }}</span></div>@endif
  @elseif ($o->status_bayar === 'belum')
    <div class="c b" style="margin-top:6px">BELUM DIBAYAR</div>
  @else
    <div class="c b" style="margin-top:6px">DIBATALKAN</div>
  @endif
  <div class="hr"></div>
  <div class="c">{{ $s['footer_struk'] }}</div>
</div>
<div class="tools"><button onclick="window.print()">Cetak ulang</button></div>
</body></html>
