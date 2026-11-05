@extends('layouts.app')
@section('title', 'Kasir')
@push('head')
<style>
  .pos { display: grid; grid-template-columns: 1fr 420px; height: 100vh; }
  .menu-side { display: flex; flex-direction: column; min-width: 0; }
  .menu-top { padding: 18px 22px 12px; display: flex; flex-direction: column; gap: 12px; border-bottom: 1px solid var(--line); }
  .menu-top .row { display: flex; gap: 12px; align-items: center; }
  .search { flex: 1; display: flex; align-items: center; gap: 10px; height: 46px; padding: 0 14px; border-radius: 12px; background: var(--s1); border: 1px solid var(--line); color: var(--dim); }
  .search input { border: 0; background: none; outline: 0; width: 100%; height: 100%; color: var(--tx); font-weight: 600; }
  .shiftinfo { font-size: 12.5px; color: var(--mut); text-align: right; line-height: 1.35; }
  .shiftinfo b { color: var(--tx); display: block; font-size: 13.5px; }
  .cats { display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none; }
  .cats button { height: 38px; padding: 0 16px; border-radius: 99px; border: 1px solid var(--line); background: var(--s1); color: var(--mut); font-weight: 700; cursor: pointer; white-space: nowrap; }
  .cats button.on { background: var(--acc); border-color: var(--acc); color: var(--acc-ink); }
  .tiles { flex: 1; overflow-y: auto; padding: 16px 22px 24px; display: grid; grid-template-columns: repeat(auto-fill, minmax(168px, 1fr)); gap: 12px; align-content: start; }
  .tile { position: relative; text-align: left; border: 1px solid var(--line); background: var(--s1); border-radius: 16px; padding: 0; cursor: pointer; overflow: hidden; display: flex; flex-direction: column; transition: transform .08s; }
  .tile:active { transform: scale(.97); }
  .tile:hover { border-color: #57493a; }
  .tile .ph { height: 92px; display: grid; place-items: center; font-size: 34px; font-weight: 800; letter-spacing: -.03em; }
  .tile .ph img { width: 100%; height: 100%; object-fit: cover; }
  .tile .tx { padding: 10px 12px 12px; }
  .tile b { display: block; font-size: 14px; line-height: 1.25; min-height: 35px; }
  .tile span { color: var(--acc); font-weight: 800; font-size: 14px; }
  .tile.off { opacity: .4; cursor: not-allowed; }
  .tile.off::after { content: "Habis"; position: absolute; top: 10px; right: 10px; background: var(--red); color: #fff; font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 99px; }
  .tile .qtag { position: absolute; top: 10px; left: 10px; min-width: 26px; height: 26px; border-radius: 13px; background: var(--acc); color: var(--acc-ink); font-weight: 800; font-size: 13px; display: grid; place-items: center; padding: 0 7px; }
  .c0 { background: #3a2b1f; color: #e9a64c; } .c1 { background: #22302a; color: #8fd0a0; } .c2 { background: #2f2a1c; color: #e8cf7a; } .c3 { background: #33241f; color: #ec9478; } .c4 { background: #262b35; color: #93b6e2; } .c5 { background: #2f2433; color: #c9a2df; }
  .order { background: var(--s1); border-left: 1px solid var(--line); display: flex; flex-direction: column; min-height: 0; }
  .order-h { padding: 16px 18px 12px; border-bottom: 1px solid var(--line); }
  .order-h .row { display: flex; gap: 8px; align-items: center; justify-content: space-between; margin-bottom: 12px; }
  .order-h h2 { margin: 0; font-size: 18px; font-weight: 800; }
  .seg { display: grid; grid-template-columns: 1fr 1fr; background: var(--s2); border-radius: 12px; padding: 4px; gap: 4px; }
  .seg button { height: 38px; border: 0; border-radius: 9px; background: none; color: var(--mut); font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; }
  .seg button.on { background: var(--s3); color: var(--tx); }
  .order-h .row2 { display: grid; grid-template-columns: 110px 1fr; gap: 8px; margin-top: 10px; }
  .order-h .in { margin: 0; height: 40px; }
  .lines { flex: 1; overflow-y: auto; padding: 6px 18px; }
  .line { display: grid; grid-template-columns: 1fr auto; gap: 4px 10px; padding: 12px 0; border-bottom: 1px dashed var(--line); }
  .line b { font-size: 14.5px; } .line .op { color: var(--mut); font-size: 12.5px; grid-column: 1; } .line .note { color: var(--amber); font-size: 12.5px; grid-column: 1; }
  .line .pr { text-align: right; font-weight: 800; }
  .line .qty { grid-column: 1; justify-self: start; }
  .qty { display: inline-flex; align-items: center; gap: 2px; background: var(--s2); border-radius: 10px; margin-top: 6px; }
  .qty button { width: 32px; height: 30px; border: 0; background: none; color: var(--tx); cursor: pointer; display: grid; place-items: center; }
  .qty span { min-width: 22px; text-align: center; font-weight: 800; }
  .line .rm { grid-column: 2; justify-self: end; align-self: end; border: 0; background: none; color: var(--dim); cursor: pointer; }
  .blank { text-align: center; color: var(--dim); padding: 50px 20px; }
  .blank .ic { width: 42px; height: 42px; opacity: .5; margin-bottom: 10px; }
  .order-f { border-top: 1px solid var(--line); padding: 14px 18px 18px; }
  .sum { display: flex; justify-content: space-between; color: var(--mut); padding: 3px 0; font-weight: 600; }
  .sum.tot { color: var(--tx); font-size: 22px; font-weight: 800; padding-top: 8px; letter-spacing: -.01em; }
  .sum a { cursor: pointer; }
  .pay { display: grid; grid-template-columns: 1fr 2fr; gap: 8px; margin-top: 12px; }
  .og { margin-bottom: 18px; } .og h4 { margin: 0 0 8px; font-size: 13px; color: var(--mut); display: flex; gap: 8px; align-items: center; }
  .og h4 .pill { height: 20px; font-size: 11px; }
  .chips { display: flex; flex-wrap: wrap; gap: 8px; }
  .chips label { cursor: pointer; } .chips input { position: absolute; opacity: 0; }
  .chips span { display: inline-flex; align-items: center; gap: 6px; height: 40px; padding: 0 14px; border-radius: 10px; border: 1px solid var(--line); background: var(--s2); font-weight: 700; }
  .chips span small { color: var(--acc); font-weight: 800; }
  .chips input:checked + span { background: var(--acc); color: var(--acc-ink); border-color: var(--acc); } .chips input:checked + span small { color: var(--acc-ink); }
  .bigqty { display: flex; align-items: center; gap: 12px; } .bigqty button { width: 44px; height: 44px; border-radius: 12px; border: 1px solid var(--line); background: var(--s2); cursor: pointer; display: grid; place-items: center; } .bigqty b { font-size: 22px; min-width: 30px; text-align: center; }
  .paytot { text-align: center; padding: 6px 0 16px; } .paytot small { color: var(--mut); font-weight: 700; } .paytot b { display: block; font-size: 38px; font-weight: 800; letter-spacing: -.02em; }
  .quick { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 10px 0 14px; } .quick button { height: 44px; border-radius: 10px; border: 1px solid var(--line); background: var(--s2); font-weight: 800; cursor: pointer; }
  .change { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; border-radius: 12px; background: var(--s2); font-weight: 800; font-size: 18px; } .change.ok { background: var(--green-c); color: var(--green); } .change.bad { background: var(--red-c); color: var(--red); }
  .done { text-align: center; padding: 10px 0; } .done .no { font-size: 56px; font-weight: 800; letter-spacing: -.03em; color: var(--acc); line-height: 1; margin: 10px 0; }
  .ob { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--line); } .ob small { display: block; color: var(--mut); }
  .badge-n { min-width: 20px; height: 20px; border-radius: 10px; background: var(--red); color: #fff; font-size: 11.5px; font-weight: 800; display: inline-grid; place-items: center; padding: 0 6px; }
</style>
@endpush
@section('full')
<div class="pos">
  <section class="menu-side">
    <div class="menu-top">
      <div class="row">
        <label class="search">{!! icon('search', 18) !!}<input id="q" placeholder="Cari menu..." autocomplete="off"></label>
        <button class="btn" id="btnOpen">{!! icon('table', 18) !!} Bayar nanti <span class="badge-n hidden" id="obN">0</span></button>
        <div class="shiftinfo"><b>{{ auth()->user()->name }}</b>Shift sejak {{ $shift->dibuka_at->format('H.i') }}</div>
      </div>
      <div class="cats" id="cats"><button class="on" data-k="">Semua</button>@foreach ($kategori as $k)<button data-k="{{ $k->id }}">{{ $k->nama }}</button>@endforeach</div>
    </div>
    <div class="tiles" id="tiles"></div>
  </section>

  <aside class="order">
    <div class="order-h">
      <div class="row"><h2>Pesanan baru</h2><button class="btn sm" id="btnClear" title="Kosongkan">{!! icon('trash', 16) !!}</button></div>
      <div class="seg" id="tipe"><button class="on" data-v="dine_in">{!! icon('table', 16) !!} Dine in</button><button data-v="takeaway">{!! icon('bag', 16) !!} Takeaway</button></div>
      <div class="row2">
        <select class="in" id="meja"><option value="">Meja</option>@for ($i = 1; $i <= max(1, $meja); $i++)<option value="{{ $i }}">Meja {{ $i }}</option>@endfor</select>
        <input class="in" id="pelanggan" placeholder="Nama pelanggan (dipanggil)" maxlength="60">
      </div>
    </div>
    <div class="lines" id="lines"></div>
    <div class="order-f">
      <div class="sum"><span>Subtotal</span><span class="num" id="sSub">Rp0</span></div>
      <div class="sum"><a id="btnDisc">Diskon <span id="sDiscLbl"></span></a><span class="num" id="sDisc">-</span></div>
      @if ($pajak > 0)<div class="sum"><span>Pajak {{ rtrim(rtrim(number_format($pajak, 2, ',', ''), '0'), ',') }}%</span><span class="num" id="sTax">Rp0</span></div>@endif
      <div class="sum tot"><span>Total</span><span class="num" id="sTot">Rp0</span></div>
      <div class="pay">
        <button class="btn lg" id="btnLater" title="Simpan, bayar setelah selesai (dine in)">Nanti</button>
        <button class="btn acc lg" id="btnPay" disabled>{!! icon('cash', 20) !!} Bayar</button>
      </div>
    </div>
  </aside>
</div>

<dialog id="dOpsi"><div class="dlg-h"><h3 id="oNama"></h3><button class="x" onclick="this.closest('dialog').close()">{!! icon('x', 18) !!}</button></div>
  <div class="dlg-b"><p class="mut" id="oDesk" style="margin-top:0"></p><div id="oGrup"></div>
    <div class="og"><h4>Catatan</h4><input class="in" id="oNote" placeholder="Contoh: es dipisah" maxlength="120"></div>
    <div class="bigqty"><button type="button" id="oMin">{!! icon('minus', 18) !!}</button><b id="oQty">1</b><button type="button" id="oPlus">{!! icon('plus', 18) !!}</button></div></div>
  <div class="dlg-f"><button class="btn acc lg block" id="oAdd">Tambah</button></div></dialog>

<dialog id="dDisc"><div class="dlg-h"><h3>Diskon</h3><button class="x" onclick="this.closest('dialog').close()">{!! icon('x', 18) !!}</button></div>
  <div class="dlg-b"><div class="seg" id="discTipe" style="margin-bottom:14px"><button class="on" data-v="persen">Persen (%)</button><button data-v="nominal">Nominal (Rp)</button></div>
    <input class="in" id="discVal" type="number" min="0" inputmode="numeric" placeholder="0">
    <div class="quick" id="discQuick" style="margin-top:12px"><button data-v="5">5%</button><button data-v="10">10%</button><button data-v="15">15%</button><button data-v="20">20%</button></div></div>
  <div class="dlg-f"><button class="btn" id="discReset">Hapus diskon</button><button class="btn acc block" id="discOk">Terapkan</button></div></dialog>

<dialog id="dPay"><div class="dlg-h"><h3 id="payTitle">Pembayaran</h3><button class="x" onclick="this.closest('dialog').close()">{!! icon('x', 18) !!}</button></div>
  <div class="dlg-b"><div class="paytot"><small>Total tagihan</small><b class="num" id="payTot"></b></div>
    <div class="seg" id="payMet" style="grid-template-columns:repeat(3,1fr)"><button class="on" data-v="tunai">{!! icon('cash', 16) !!} Tunai</button><button data-v="qris">{!! icon('qr', 16) !!} QRIS</button><button data-v="debit">{!! icon('card', 16) !!} Debit</button></div>
    <div id="payCash" style="margin-top:14px"><input class="in num" id="payIn" type="number" min="0" inputmode="numeric" placeholder="Uang diterima" style="font-size:20px;height:54px">
      <div class="quick" id="payQuick"></div><div class="change" id="payChange"><span>Kembalian</span><span class="num">Rp0</span></div></div>
    <p class="mut sm" id="payNon" style="display:none;margin:16px 0 0">Pastikan pembayaran sudah berhasil di mesin EDC / aplikasi QRIS sebelum menekan Selesai.</p>
    <p class="alert err hidden" id="payErr" style="margin:14px 0 0"></p></div>
  <div class="dlg-f"><button class="btn acc lg block" id="payOk">Selesai</button></div></dialog>

<dialog id="dDone"><div class="dlg-b done"><span class="pill green">{!! icon('check', 14) !!} Pesanan tersimpan</span><div class="no" id="dnNo"></div><p class="mut" id="dnInfo" style="margin:0 0 6px"></p><div class="change ok" id="dnChange" style="margin-top:12px"><span>Kembalian</span><span class="num"></span></div></div>
  <div class="dlg-f"><a class="btn lg" id="dnPrint" target="_blank" rel="noopener">{!! icon('printer', 18) !!} Struk</a><button class="btn acc lg block" id="dnNext">Pesanan baru</button></div></dialog>

<dialog id="dOpen"><div class="dlg-h"><h3>Belum dibayar</h3><button class="x" onclick="this.closest('dialog').close()">{!! icon('x', 18) !!}</button></div><div class="dlg-b" id="obList"></div></dialog>
@endsection

@push('scripts')
<script>
(function () {
  var MENU = @json($menu), PAJAK = {{ $pajak }}, $ = function (s) { return document.querySelector(s); };
  var cart = [], st = { kat: '', q: '', tipe: 'dine_in', disc: { tipe: 'persen', v: 0 } }, pend = null, payFor = null;
  var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
  var catIdx = {}; document.querySelectorAll('#cats [data-k]').forEach(function (b, i) { if (b.dataset.k) catIdx[b.dataset.k] = (i - 1) % 6; });
  function seg(id, cb) { document.querySelectorAll(id + ' button').forEach(function (b) { b.onclick = function () { document.querySelectorAll(id + ' button').forEach(function (x) { x.classList.toggle('on', x === b); }); cb(b.dataset.v); }; }); }
  function setSeg(id, v) { document.querySelectorAll(id + ' button').forEach(function (x) { x.classList.toggle('on', x.dataset.v === v); }); }

  function tiles() {
    var inCart = {}; cart.forEach(function (c) { inCart[c.id] = (inCart[c.id] || 0) + c.qty; });
    var list = MENU.filter(function (m) { return (!st.kat || String(m.k) === st.kat) && (!st.q || m.nama.toLowerCase().indexOf(st.q) > -1); });
    $('#tiles').innerHTML = list.map(function (m) {
      var ini = m.nama.split(' ').map(function (w) { return w[0]; }).slice(0, 2).join('');
      return '<button class="tile' + (m.tersedia ? '' : ' off') + '" data-id="' + m.id + '">' + (inCart[m.id] ? '<span class="qtag">' + inCart[m.id] + '</span>' : '') +
        '<div class="ph c' + (catIdx[m.k] || 0) + '">' + (m.foto ? '<img src="' + esc(m.foto) + '" alt="">' : esc(ini)) + '</div><div class="tx"><b>' + esc(m.nama) + '</b><span>' + rp(m.harga) + '</span></div></button>';
    }).join('') || '<p class="empty" style="grid-column:1/-1">Menu tidak ditemukan.</p>';
  }
  document.querySelectorAll('#cats button').forEach(function (b) { b.onclick = function () { document.querySelectorAll('#cats button').forEach(function (x) { x.classList.toggle('on', x === b); }); st.kat = b.dataset.k; tiles(); }; });
  $('#q').oninput = function () { st.q = this.value.trim().toLowerCase(); tiles(); };
  $('#tiles').onclick = function (e) {
    var t = e.target.closest('.tile'); if (!t || t.classList.contains('off')) return;
    var m = MENU.find(function (x) { return x.id == t.dataset.id; });
    if (!m.grup.length) return add(m, [], '', 1);
    openOpsi(m);
  };

  // Dialog opsi
  function openOpsi(m) {
    pend = { m: m, qty: 1 }; $('#oNama').textContent = m.nama + ' · ' + rp(m.harga); $('#oDesk').textContent = m.desk || ''; $('#oNote').value = ''; $('#oQty').textContent = 1;
    $('#oGrup').innerHTML = m.grup.map(function (g) {
      return '<div class="og"><h4>' + esc(g.nama) + (g.wajib ? ' <span class="pill amber">wajib</span>' : ' <span class="pill">opsional</span>') + '</h4><div class="chips">' +
        g.opsi.map(function (o, i) { return '<label><input type="' + (g.multi ? 'checkbox' : 'radio') + '" name="g' + g.id + '" value="' + o.id + '"' + (g.wajib && i === 0 ? ' checked' : '') + '><span>' + esc(o.nama) + (o.tambahan ? ' <small>+' + rp(o.tambahan).replace('Rp', '') + '</small>' : '') + '</span></label>'; }).join('') + '</div></div>';
    }).join('');
    $('#dOpsi').showModal();
  }
  $('#oMin').onclick = function () { pend.qty = Math.max(1, pend.qty - 1); $('#oQty').textContent = pend.qty; };
  $('#oPlus').onclick = function () { pend.qty = Math.min(99, pend.qty + 1); $('#oQty').textContent = pend.qty; };
  $('#oAdd').onclick = function () {
    var ids = [].map.call(document.querySelectorAll('#oGrup input:checked'), function (i) { return +i.value; });
    add(pend.m, ids, $('#oNote').value.trim(), pend.qty); $('#dOpsi').close();
  };
  function add(m, ids, note, qty) {
    var ops = [], tamb = 0;
    m.grup.forEach(function (g) { g.opsi.forEach(function (o) { if (ids.indexOf(o.id) > -1) { ops.push(o.nama); tamb += o.tambahan; } }); });
    var key = m.id + '|' + ids.slice().sort().join(',') + '|' + note;
    var ex = cart.find(function (c) { return c.key === key; });
    if (ex) ex.qty += qty; else cart.push({ key: key, id: m.id, nama: m.nama, opsi: ids, label: ops.join(', '), note: note, harga: m.harga + tamb, qty: qty });
    render();
  }

  function hitung() {
    var sub = cart.reduce(function (s, c) { return s + c.harga * c.qty; }, 0);
    var d = st.disc.tipe === 'persen' ? Math.round(sub * Math.min(100, st.disc.v) / 100) : Math.min(sub, st.disc.v);
    var tax = Math.round((sub - d) * PAJAK / 100);
    return { sub: sub, disc: d, tax: tax, tot: sub - d + tax };
  }
  function render() {
    var h = hitung();
    $('#lines').innerHTML = cart.length ? cart.map(function (c, i) {
      return '<div class="line"><b>' + esc(c.nama) + '</b><span class="pr num">' + rp(c.harga * c.qty) + '</span>' + (c.label ? '<span class="op">' + esc(c.label) + '</span>' : '') +
        (c.note ? '<span class="note">' + esc(c.note) + '</span>' : '') + '<div class="qty"><button data-i="' + i + '" data-d="-1">{!! icon('minus', 15) !!}</button><span>' + c.qty + '</span><button data-i="' + i + '" data-d="1">{!! icon('plus', 15) !!}</button></div>' +
        '<button class="rm" data-rm="' + i + '" title="Hapus">{!! icon('trash', 16) !!}</button></div>';
    }).join('') : '<div class="blank">{!! icon('cup', 42) !!}<div>Belum ada item.<br>Pilih menu di sebelah kiri.</div></div>';
    $('#sSub').textContent = rp(h.sub); $('#sDisc').textContent = h.disc ? '-' + rp(h.disc) : '-';
    $('#sDiscLbl').textContent = st.disc.v ? (st.disc.tipe === 'persen' ? st.disc.v + '%' : '') : '+'; if ($('#sTax')) $('#sTax').textContent = rp(h.tax);
    $('#sTot').textContent = rp(h.tot); $('#btnPay').disabled = !cart.length; $('#btnLater').disabled = !cart.length || st.tipe !== 'dine_in';
    tiles();
  }
  $('#lines').onclick = function (e) {
    var b = e.target.closest('button'); if (!b) return;
    if (b.dataset.rm !== undefined) cart.splice(+b.dataset.rm, 1);
    else { var c = cart[+b.dataset.i]; c.qty = Math.max(0, Math.min(99, c.qty + +b.dataset.d)); if (!c.qty) cart.splice(+b.dataset.i, 1); }
    render();
  };
  seg('#tipe', function (v) { st.tipe = v; $('#meja').disabled = v !== 'dine_in'; if (v !== 'dine_in') $('#meja').value = ''; render(); });
  $('#btnClear').onclick = function () { if (cart.length && !confirm('Kosongkan pesanan?')) return; reset(); };
  function reset() { cart = []; st.disc = { tipe: 'persen', v: 0 }; $('#pelanggan').value = ''; $('#meja').value = ''; setSeg('#tipe', 'dine_in'); st.tipe = 'dine_in'; $('#meja').disabled = false; render(); }

  // Diskon
  var dt = 'persen'; seg('#discTipe', function (v) { dt = v; $('#discQuick').style.display = v === 'persen' ? '' : 'none'; });
  $('#btnDisc').onclick = function () { dt = st.disc.tipe; setSeg('#discTipe', dt); $('#discVal').value = st.disc.v || ''; $('#dDisc').showModal(); };
  $('#discQuick').onclick = function (e) { var b = e.target.closest('button'); if (b) $('#discVal').value = b.dataset.v; };
  $('#discOk').onclick = function () { var v = Math.max(0, parseInt($('#discVal').value || '0', 10)); st.disc = { tipe: dt, v: dt === 'persen' ? Math.min(100, v) : v }; $('#dDisc').close(); render(); };
  $('#discReset').onclick = function () { st.disc = { tipe: 'persen', v: 0 }; $('#dDisc').close(); render(); };

  // Pembayaran
  var met = 'tunai';
  seg('#payMet', function (v) { met = v; $('#payCash').style.display = v === 'tunai' ? '' : 'none'; $('#payNon').style.display = v === 'tunai' ? 'none' : ''; kembalian(); });
  function openPay(total, title, order) {
    payFor = order || null; met = 'tunai'; setSeg('#payMet', 'tunai'); $('#payCash').style.display = ''; $('#payNon').style.display = 'none';
    $('#payTitle').textContent = title; $('#payTot').textContent = rp(total); $('#payTot').dataset.v = total; $('#payIn').value = ''; $('#payErr').classList.add('hidden');
    var opts = [total], pec = [5000, 10000, 20000, 50000, 100000];
    pec.forEach(function (p) { var v = Math.ceil(total / p) * p; if (opts.indexOf(v) < 0) opts.push(v); });
    opts = opts.sort(function (a, b) { return a - b; }).slice(0, 4);
    $('#payQuick').innerHTML = opts.map(function (v, i) { return '<button type="button" data-v="' + v + '">' + (i === 0 ? 'Uang pas' : rp(v).replace('Rp', '')) + '</button>'; }).join('');
    kembalian(); $('#dPay').showModal(); setTimeout(function () { $('#payIn').focus(); }, 50);
  }
  $('#payQuick').onclick = function (e) { var b = e.target.closest('button'); if (b) { $('#payIn').value = b.dataset.v; kembalian(); } };
  $('#payIn').oninput = kembalian;
  function kembalian() {
    var tot = +$('#payTot').dataset.v, v = parseInt($('#payIn').value || '0', 10), el = $('#payChange');
    if (met !== 'tunai') { $('#payOk').disabled = false; return; }
    el.className = 'change ' + (v >= tot ? 'ok' : v ? 'bad' : ''); el.lastChild.textContent = v >= tot ? rp(v - tot) : v ? 'Kurang ' + rp(tot - v) : 'Rp0';
    $('#payOk').disabled = v < tot;
  }
  $('#btnPay').onclick = function () { openPay(hitung().tot, 'Pembayaran'); };
  $('#payIn').onkeydown = function (e) { if (e.key === 'Enter' && !$('#payOk').disabled) $('#payOk').click(); };
  function payload(extra) {
    return Object.assign({ items: cart.map(function (c) { return { menu_id: c.id, qty: c.qty, opsi: c.opsi, catatan: c.note || null }; }), tipe: st.tipe, meja: $('#meja').value || null,
      pelanggan: $('#pelanggan').value.trim() || null, diskon_tipe: st.disc.tipe, diskon: st.disc.v }, extra);
  }
  function selesai(r, title) {
    $('#dnNo').textContent = r.label; $('#dnInfo').textContent = title + ' · total ' + rp(r.total);
    $('#dnChange').style.display = r.kembalian > 0 ? '' : 'none'; $('#dnChange').lastChild.textContent = rp(r.kembalian);
    $('#dnPrint').href = r.struk; $('#dDone').showModal(); loadOpen();
  }
  $('#payOk').onclick = function () {
    var btn = this; btn.disabled = true; $('#payErr').classList.add('hidden');
    var data = { metode: met, dibayar: met === 'tunai' ? parseInt($('#payIn').value || '0', 10) : null };
    var req = payFor ? api(BASE + '/kasir/order/' + payFor + '/bayar', data) : api('{{ route('kasir.store') }}', payload(data));
    req.then(function (r) { $('#dPay').close(); if (!payFor) reset(); selesai(r, payFor ? 'Tagihan lunas' : 'Lunas'); })
      .catch(function (e) { $('#payErr').textContent = e.message; $('#payErr').classList.remove('hidden'); }).then(function () { btn.disabled = false; });
  };
  $('#btnLater').onclick = function () {
    if (!$('#meja').value) { toast('Pilih nomor meja dulu'); $('#meja').focus(); return; }
    var btn = this; btn.disabled = true;
    api('{{ route('kasir.store') }}', payload({ open_bill: true })).then(function (r) { reset(); selesai(r, 'Dikirim ke barista, bayar nanti'); })
      .catch(function (e) { toast(e.message); }).then(function () { btn.disabled = false; });
  };
  $('#dnNext').onclick = function () { $('#dDone').close(); $('#q').focus(); };

  // Tagihan belum dibayar (dine in)
  var OPEN = [];
  function loadOpen() { api('{{ route('kasir.open') }}').then(function (r) { OPEN = r; $('#obN').textContent = r.length; $('#obN').classList.toggle('hidden', !r.length); }); }
  $('#btnOpen').onclick = function () {
    $('#obList').innerHTML = OPEN.length ? OPEN.map(function (o) {
      return '<div class="ob"><div><b>' + o.label + ' · Meja ' + esc(o.meja) + (o.pelanggan ? ' · ' + esc(o.pelanggan) : '') + '</b><small>' + o.waktu + ' · ' + esc(o.items) + '</small></div><button class="btn acc sm" data-id="' + o.id + '" data-t="' + o.total + '">' + rp(o.total) + '</button></div>';
    }).join('') : '<p class="empty">Tidak ada tagihan yang menunggu.</p>';
    $('#dOpen').showModal();
  };
  $('#obList').onclick = function (e) { var b = e.target.closest('button'); if (!b) return; $('#dOpen').close(); openPay(+b.dataset.t, 'Bayar tagihan', b.dataset.id); };

  render(); loadOpen(); setInterval(loadOpen, 30000);
  // Menu habis bisa diubah barista: segarkan ketersediaan tiap 60 detik tanpa mengganggu keranjang
  setInterval(function () { api('{{ route('kasir.menu') }}').then(function (m) { MENU = m; tiles(); }).catch(function () {}); }, 60000);
})();
</script>
@endpush
