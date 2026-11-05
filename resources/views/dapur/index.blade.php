@extends('layouts.app')
@section('title', 'Barista')
@push('head')
<style>
  .kds { height: 100vh; display: flex; flex-direction: column; }
  .kds-h { display: flex; align-items: center; gap: 14px; padding: 14px 22px; border-bottom: 1px solid var(--line); }
  .kds-h h1 { margin: 0; font-size: 22px; font-weight: 800; margin-right: auto; }
  .kpi { text-align: right; line-height: 1.2; } .kpi b { display: block; font-size: 20px; } .kpi small { color: var(--mut); font-size: 12px; font-weight: 700; }
  .clock { font-size: 22px; font-weight: 800; padding: 0 6px; }
  .cols { flex: 1; display: grid; grid-template-columns: repeat(3, 1fr); min-height: 0; }
  .col { display: flex; flex-direction: column; min-height: 0; border-right: 1px solid var(--line); }
  .col:last-child { border-right: 0; }
  .col-h { display: flex; align-items: center; gap: 10px; padding: 14px 18px; font-weight: 800; font-size: 15px; }
  .col-h i { width: 10px; height: 10px; border-radius: 50%; } .col-h span { margin-left: auto; background: var(--s2); min-width: 30px; height: 26px; border-radius: 13px; display: grid; place-items: center; font-size: 13px; }
  .col-b { flex: 1; overflow-y: auto; padding: 0 14px 16px; display: flex; flex-direction: column; gap: 12px; }
  .tk { background: var(--s1); border: 1px solid var(--line); border-radius: 16px; overflow: hidden; }
  .tk.new { animation: flash 1.2s ease 3; }
  @keyframes flash { 50% { border-color: var(--acc); box-shadow: 0 0 0 3px rgba(233,166,76,.35); } }
  .tk-h { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-bottom: 1px solid var(--line); }
  .tk-h .no { font-size: 22px; font-weight: 800; letter-spacing: -.02em; }
  .tk-h .meta { line-height: 1.25; font-size: 12.5px; color: var(--mut); font-weight: 700; } .tk-h .meta b { color: var(--tx); display: block; font-size: 13.5px; }
  .tk-h .t { margin-left: auto; font-weight: 800; font-size: 15px; font-variant-numeric: tabular-nums; padding: 4px 10px; border-radius: 9px; background: var(--s2); }
  .tk-h .t.warn { background: var(--amber-c); color: var(--amber); } .tk-h .t.late { background: var(--red-c); color: var(--red); }
  .tk ul { list-style: none; margin: 0; padding: 10px 14px; }
  .tk li { padding: 6px 0; display: grid; grid-template-columns: 34px 1fr; } .tk li b.q { font-size: 17px; color: var(--acc); }
  .tk li .n { font-weight: 800; font-size: 15px; } .tk li small { display: block; color: var(--mut); font-size: 12.5px; } .tk li em { display: block; color: var(--amber); font-style: normal; font-size: 12.5px; font-weight: 700; }
  .tk-f { display: flex; gap: 8px; padding: 0 14px 14px; }
  .tk-f .btn { flex: 1; height: 46px; } .tk-f .btn.sm { flex: 0 0 46px; padding: 0; }
  .col.c-siap .tk { border-color: rgba(116,196,131,.35); }
  .blank { color: var(--dim); text-align: center; padding: 40px 10px; font-weight: 600; }
  .stokbar { display: flex; gap: 6px; flex-wrap: wrap; } .stokbar button { height: 32px; padding: 0 10px; border-radius: 8px; border: 1px solid var(--line); background: var(--s2); color: var(--tx); font-weight: 700; font-size: 12.5px; cursor: pointer; } .stokbar button.off { background: var(--red-c); color: var(--red); border-color: transparent; text-decoration: line-through; }
</style>
@endpush
@section('full')
<div class="kds">
  <div class="kds-h">
    <h1>Layar Barista</h1>
    <div class="kpi"><b id="kRata">-</b><small>rata-rata menit</small></div>
    <div class="kpi"><b id="kDone">0</b><small>selesai hari ini</small></div>
    <button class="btn" id="btnStok">{!! icon('list', 18) !!} Stok menu</button>
    <button class="btn" id="btnSound" title="Bunyi saat ada pesanan baru">{!! icon('bell', 18) !!} <span>Bunyi aktif</span></button>
    <button class="btn" id="btnFull" title="Layar penuh">{!! icon('maximize', 18) !!}</button>
    <div class="clock num" id="clock"></div>
  </div>
  <div class="cols">
    <section class="col c-baru"><div class="col-h"><i style="background:var(--amber)"></i>Pesanan baru<span id="nBaru">0</span></div><div class="col-b" id="cBaru"></div></section>
    <section class="col c-dibuat"><div class="col-h"><i style="background:var(--blue)"></i>Sedang dibuat<span id="nDibuat">0</span></div><div class="col-b" id="cDibuat"></div></section>
    <section class="col c-siap"><div class="col-h"><i style="background:var(--green)"></i>Siap diambil<span id="nSiap">0</span></div><div class="col-b" id="cSiap"></div></section>
  </div>
</div>
<dialog id="dStok"><div class="dlg-h"><h3>Stok menu</h3><button class="x" onclick="this.closest('dialog').close()">{!! icon('x', 18) !!}</button></div>
  <div class="dlg-b"><p class="mut sm" style="margin-top:0">Ketuk menu yang sedang habis. Kasir tidak bisa memesannya sampai diaktifkan lagi.</p>
    <div class="stokbar">@foreach ($menu as $m)<button data-id="{{ $m->id }}" class="{{ $m->tersedia ? '' : 'off' }}">{{ $m->nama }}</button>@endforeach</div></div></dialog>
@endsection
@push('scripts')
<script>
(function () {
  var $ = function (s) { return document.querySelector(s); }, seen = null, offset = 0, sound = true, data = [];
  var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
  try { sound = localStorage.getItem('kds_sound') !== '0'; } catch (e) {}
  function setSoundLbl() { $('#btnSound span').textContent = sound ? 'Bunyi aktif' : 'Bunyi mati'; }
  setSoundLbl();
  $('#btnSound').onclick = function () { sound = !sound; try { localStorage.setItem('kds_sound', sound ? '1' : '0'); } catch (e) {} setSoundLbl(); if (sound) beep(); };
  $('#btnFull').onclick = function () { document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen(); };
  // Bunyi "ding" sederhana tanpa file audio
  var ac = null;
  function beep() {
    try { ac = ac || new (window.AudioContext || window.webkitAudioContext)(); [0, .18].forEach(function (d, i) { var o = ac.createOscillator(), g = ac.createGain(); o.type = 'sine'; o.frequency.value = i ? 1320 : 880;
      g.gain.setValueAtTime(.0001, ac.currentTime + d); g.gain.exponentialRampToValueAtTime(.25, ac.currentTime + d + .02); g.gain.exponentialRampToValueAtTime(.0001, ac.currentTime + d + .35); o.connect(g); g.connect(ac.destination); o.start(ac.currentTime + d); o.stop(ac.currentTime + d + .4); }); } catch (e) {}
  }
  function mmss(s) { s = Math.max(0, s); return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2); }
  function now() { return Math.floor(Date.now() / 1000) + offset; }
  function card(o, isNew) {
    var age = now() - o.masuk, cls = age > 600 ? 'late' : age > 300 ? 'warn' : '';
    var next = { baru: ['Mulai buat', 'acc'], dibuat: ['Siap', 'acc'], siap: ['Sudah diambil', ''] }[o.status];
    return '<article class="tk' + (isNew ? ' new' : '') + '" data-id="' + o.id + '"><div class="tk-h"><span class="no">' + o.label + '</span><div class="meta"><b>' + esc(o.pelanggan || '-') + '</b>' +
      (o.tipe === 'dine_in' ? 'Meja ' + esc(o.meja) : 'Takeaway') + (o.belum_bayar ? ' · belum bayar' : '') + '</div><span class="t ' + (o.status === 'siap' ? '' : cls) + '" data-masuk="' + o.masuk + '" data-siap="' + (o.siap || '') + '">' +
      (o.status === 'siap' ? 'siap ' + mmss(now() - o.siap) : mmss(age)) + '</span></div><ul>' +
      o.items.map(function (i) { return '<li><b class="q">' + i.qty + 'x</b><div><span class="n">' + esc(i.nama) + '</span>' + (i.opsi ? '<small>' + esc(i.opsi) + '</small>' : '') + (i.catatan ? '<em>' + esc(i.catatan) + '</em>' : '') + '</div></li>'; }).join('') +
      '</ul><div class="tk-f">' + (o.status !== 'baru' ? '<button class="btn sm" data-arah="mundur" title="Kembalikan">{!! icon('undo', 18) !!}</button>' : '') + '<button class="btn ' + next[1] + '" data-arah="maju">' + next[0] + '</button></div></article>';
  }
  function render(isFirst) {
    var baru = [];
    ['baru', 'dibuat', 'siap'].forEach(function (s) {
      var list = data.filter(function (o) { return o.status === s; });
      var col = $('#c' + s.charAt(0).toUpperCase() + s.slice(1));
      col.innerHTML = list.map(function (o) { var n = seen && !seen[o.id]; if (n) baru.push(o.id); return card(o, n); }).join('') || '<div class="blank">Kosong</div>';
      $('#n' + s.charAt(0).toUpperCase() + s.slice(1)).textContent = list.length;
    });
    if (baru.length && sound && !isFirst) beep();
    seen = {}; data.forEach(function (o) { seen[o.id] = 1; });
  }
  function load(first) {
    api('{{ route('dapur.data') }}').then(function (r) {
      offset = r.server - Math.floor(Date.now() / 1000); data = r.orders; render(first);
      $('#kDone').textContent = r.selesai_hari_ini; $('#kRata').textContent = r.rata_menit || '-';
    }).catch(function () {});
  }
  document.querySelector('.cols').onclick = function (e) {
    var b = e.target.closest('button[data-arah]'); if (!b) return;
    var id = b.closest('.tk').dataset.id; b.disabled = true;
    api(BASE + '/dapur/' + id + '/status', { arah: b.dataset.arah }).then(function (r) {
      var o = data.find(function (x) { return x.id == id; });
      if (r.status === 'diambil') data = data.filter(function (x) { return x.id != id; }); else { o.status = r.status; if (r.status === 'siap') o.siap = now(); }
      render(); load();
    }).catch(function (e) { toast(e.message); b.disabled = false; });
  };
  setInterval(function () {
    var d = new Date((now()) * 1000); $('#clock').textContent = ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
    document.querySelectorAll('.tk-h .t').forEach(function (t) {
      if (t.dataset.siap) { t.textContent = 'siap ' + mmss(now() - t.dataset.siap); return; }
      var age = now() - t.dataset.masuk; t.textContent = mmss(age); t.className = 't ' + (age > 600 ? 'late' : age > 300 ? 'warn' : '');
    });
  }, 1000);
  $('#btnStok').onclick = function () { $('#dStok').showModal(); };
  $('#dStok').onclick = function (e) { var b = e.target.closest('button[data-id]'); if (!b) return; api(BASE + '/dapur/menu/' + b.dataset.id + '/tersedia', {}).then(function (r) { b.classList.toggle('off', !r.tersedia); toast(b.textContent + (r.tersedia ? ' tersedia lagi' : ' ditandai habis')); }); };
  load(true); setInterval(load, 4000);
})();
</script>
@endpush
