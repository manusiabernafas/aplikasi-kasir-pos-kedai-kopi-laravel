<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title') · {{ setting('nama_toko') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v=1">
@stack('head')
</head>
<body>
@php($u = auth()->user())
<div class="app">
  <nav class="rail">
    <div class="logo" title="{{ setting('nama_toko') }}">{!! icon('cup', 24) !!}</div>
    @if ($u->punya('owner', 'kasir'))
      <a href="{{ route('kasir') }}" class="{{ request()->routeIs('kasir') ? 'on' : '' }}">{!! icon('cash', 22) !!}Kasir</a>
    @endif
    <a href="{{ route('dapur') }}" class="{{ request()->routeIs('dapur') ? 'on' : '' }}">{!! icon('chef', 22) !!}Barista</a>
    @if ($u->punya('owner', 'kasir'))
      <a href="{{ route('riwayat') }}" class="{{ request()->routeIs('riwayat*') ? 'on' : '' }}">{!! icon('receipt', 22) !!}Riwayat</a>
      <a href="{{ route('shift') }}" class="{{ request()->routeIs('shift*') ? 'on' : '' }}">{!! icon('clock', 22) !!}Shift</a>
    @endif
    @if ($u->punya('owner'))
      <a href="{{ route('laporan') }}" class="{{ request()->routeIs('laporan*') ? 'on' : '' }}">{!! icon('chart', 22) !!}Laporan</a>
      <a href="{{ route('menu') }}" class="{{ request()->routeIs('menu*') ? 'on' : '' }}">{!! icon('list', 22) !!}Menu</a>
      <a href="{{ route('pengguna') }}" class="{{ request()->routeIs('pengguna*') ? 'on' : '' }}">{!! icon('users', 22) !!}Pengguna</a>
      <a href="{{ route('pengaturan') }}" class="{{ request()->routeIs('pengaturan*') ? 'on' : '' }}">{!! icon('settings', 22) !!}Atur</a>
    @endif
    <span class="sp"></span>
    <a href="{{ route('layar', array_filter(['kode' => setting('kode_layar')])) }}" target="_blank" rel="noopener">{!! icon('tv', 22) !!}Layar</a>
    <form method="post" action="{{ route('logout') }}">@csrf<button title="Keluar">{!! icon('logout', 22) !!}Keluar</button></form>
    <div class="who" title="{{ $u->name }} · {{ \App\Models\User::ROLES[$u->role] }}">{{ mb_substr($u->name, 0, 1) }}</div>
  </nav>
  <main class="main">
    @hasSection('full')
      @yield('full')
    @else
      <div class="page">
        @if (session('ok'))<div class="alert">{{ session('ok') }}</div>@endif
        @if (session('error'))<div class="alert err">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif
        @yield('content')
      </div>
    @endif
  </main>
</div>
<script>
  window.CSRF = document.querySelector('meta[name=csrf-token]').content;
  window.BASE = @json(rtrim(url('/'), '/'));
  window.rp = function (n) { return 'Rp' + Math.round(n).toLocaleString('id-ID'); };
  window.api = function (url, data, method) {
    return fetch(url, { method: method || (data ? 'POST' : 'GET'), headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': window.CSRF }, body: data ? JSON.stringify(data) : undefined, credentials: 'same-origin' })
      .then(function (r) { return r.json().then(function (j) { if (!r.ok) { var m = j.message || 'Terjadi kesalahan'; if (j.errors) m = Object.values(j.errors)[0][0]; throw new Error(m); } return j; }); });
  };
  window.toast = function (t) { var el = document.createElement('div'); el.className = 'toast'; el.textContent = t; document.body.appendChild(el); setTimeout(function () { el.remove(); }, 2400); };
</script>
@stack('scripts')
</body>
</html>
