<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pesanan Siap · {{ setting('nama_toko') }}</title>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="{{ asset('css/app.css') }}?v=1">
<style>
  body { overflow: hidden; } .tv { height: 100vh; display: grid; grid-template-columns: 1.6fr 1fr; }
  .ready { background: var(--acc); color: var(--acc-ink); padding: 5vh 4vw; display: flex; flex-direction: column; }
  .ready h1, .prog h1 { margin: 0 0 3vh; font-size: 3.2vw; font-weight: 800; letter-spacing: -.02em; display: flex; justify-content: space-between; align-items: baseline; }
  .ready h1 small { font-size: 1.5vw; font-weight: 700; opacity: .75; }
  .rgrid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 2vh 2vw; align-content: start; }
  .rgrid div { background: var(--acc-ink); color: var(--acc); border-radius: 2vw; padding: 2.2vh 2vw; display: flex; align-items: baseline; gap: 1.2vw; }
  .rgrid div.new { animation: pop 1s ease 3; } @keyframes pop { 50% { transform: scale(1.04); } }
  .rgrid b { font-size: 5.4vw; font-weight: 800; letter-spacing: -.03em; line-height: 1; } .rgrid span { font-size: 2vw; color: #f4ede3; font-weight: 700; }
  .prog { padding: 5vh 3vw; display: flex; flex-direction: column; } .prog h1 { color: var(--mut); font-size: 2.4vw; }
  .plist { display: flex; flex-direction: column; gap: 1.4vh; } .plist div { display: flex; justify-content: space-between; align-items: baseline; padding: 1.6vh 1.4vw; border-radius: 1.2vw; background: var(--s1); }
  .plist b { font-size: 2.6vw; font-weight: 800; } .plist span { color: var(--mut); font-size: 1.6vw; font-weight: 700; }
  .foot { margin-top: auto; color: var(--dim); font-size: 1.3vw; font-weight: 700; display: flex; justify-content: space-between; } .ready .foot { color: rgba(42,26,6,.65); }
  .none { font-size: 2vw; opacity: .7; font-weight: 700; }
</style></head>
<body><div class="tv">
  <section class="ready"><h1>Pesanan siap diambil <small id="clock"></small></h1><div class="rgrid" id="ready"></div><div class="foot"><span>{{ setting('nama_toko') }}</span><span>Silakan ambil di meja barista</span></div></section>
  <section class="prog"><h1>Sedang disiapkan</h1><div class="plist" id="prog"></div><div class="foot"><span>Terima kasih sudah menunggu</span></div></section>
</div>
<script>
(function () {
  var URL = @json(route('layar.data', array_filter(['kode' => $kode]))), seen = null;
  var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
  function load() {
    fetch(URL, { headers: { Accept: 'application/json' } }).then(function (r) { return r.json(); }).then(function (d) {
      document.getElementById('ready').innerHTML = d.siap.map(function (o) { var n = seen && !seen[o.id]; return '<div class="' + (n ? 'new' : '') + '"><b>' + o.label + '</b><span>' + esc(o.nama || '') + '</span></div>'; }).join('') || '<p class="none">Belum ada pesanan yang siap.</p>';
      document.getElementById('prog').innerHTML = d.proses.map(function (o) { return '<div><b>' + o.label + '</b><span>' + esc(o.nama || '') + '</span></div>'; }).join('') || '<p class="none" style="color:var(--dim)">-</p>';
      seen = {}; d.siap.forEach(function (o) { seen[o.id] = 1; });
    }).catch(function () {});
  }
  function clock() { var d = new Date(); document.getElementById('clock').textContent = ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2); }
  load(); clock(); setInterval(load, 5000); setInterval(clock, 10000);
})();
</script></body></html>
