<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Masuk · {{ setting('nama_toko') }}</title>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="{{ asset('css/app.css') }}?v=1">
<style>.lg{min-height:100vh;display:grid;grid-template-columns:1.1fr 1fr}.lg-l{background:var(--acc);color:var(--acc-ink);padding:56px;display:flex;flex-direction:column;justify-content:space-between}.lg-l h1{font-size:54px;line-height:1.02;letter-spacing:-.03em;margin:0;font-weight:800}.lg-l p{font-size:17px;max-width:420px}.lg-r{display:grid;place-items:center;padding:24px}.lg-r form{width:min(380px,100%)}.lg-r h2{font-size:24px;margin:0 0 6px}.demo{margin-top:22px;font-size:13px;color:var(--mut);line-height:1.8}.demo code{background:var(--s2);padding:2px 7px;border-radius:6px;color:var(--tx)}@media(max-width:800px){.lg{grid-template-columns:1fr}.lg-l{display:none}}</style></head>
<body><div class="lg">
  <div class="lg-l"><div class="logo" style="background:var(--acc-ink);color:var(--acc)">{!! icon('cup', 24) !!}</div>
    <div><h1>{{ setting('nama_toko') }}</h1><p>Kasir, layar barista, dan laporan penjualan kedai dalam satu aplikasi.</p></div><small>{{ setting('alamat') }}</small></div>
  <div class="lg-r"><form method="post" action="{{ url('/login') }}">@csrf
    <h2>Masuk</h2><p class="mut" style="margin:0 0 22px">Gunakan akun yang diberikan pemilik kedai</p>
    @if (session('error'))<div class="alert err">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif
    <label class="f">Username<input name="username" value="{{ old('username') }}" required autofocus autocomplete="username"></label>
    <label class="f">Password<input type="password" name="password" required autocomplete="current-password"></label>
    <button class="btn acc lg block">Masuk {!! icon('arrow', 18) !!}</button>
    <div class="demo">Akun demo (password <code>admin123</code>):<br><code>owner</code> pemilik · <code>kasir</code> kasir · <code>barista</code> barista</div>
  </form></div>
</div></body></html>
