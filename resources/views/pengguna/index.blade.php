@extends('layouts.app')
@section('title', 'Pengguna')
@section('content')
<div class="head"><div><h1>Pengguna</h1><p>Pemilik melihat semua menu · Kasir: kasir, shift, riwayat · Barista: layar barista</p></div>
  <div class="acts"><button class="btn acc" onclick="isi({})">{!! icon('plus', 18) !!} Tambah pengguna</button></div></div>
<div class="card"><table class="tbl"><thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>Status</th><th></th></tr></thead><tbody>
  @foreach ($users as $u)<tr><td><b>{{ $u->name }}</b></td><td>{{ $u->username }}</td><td><span class="pill {{ ['owner' => 'acc', 'kasir' => 'blue', 'barista' => 'green'][$u->role] }}">{{ \App\Models\User::ROLES[$u->role] }}</span></td>
    <td>{!! $u->aktif ? '<span class="pill green">Aktif</span>' : '<span class="pill red">Nonaktif</span>' !!}</td>
    <td class="r"><button class="btn sm" onclick="isi({{ json_encode(['id' => $u->id, 'name' => $u->name, 'username' => $u->username, 'role' => $u->role, 'aktif' => $u->aktif]) }})">{!! icon('edit', 15) !!}</button></td></tr>@endforeach
</tbody></table></div>
<dialog id="dUser"><form method="post" action="{{ route('pengguna.simpan') }}">@csrf<input type="hidden" name="id">
  <div class="dlg-h"><h3 id="uT">Pengguna</h3><button type="button" class="x" onclick="this.closest('dialog').close()">{!! icon('x', 18) !!}</button></div>
  <div class="dlg-b"><label class="f">Nama<input name="name" required maxlength="80"></label>
    <div class="grid2" style="gap:12px"><label class="f">Username<input name="username" required maxlength="50" pattern="[A-Za-z0-9_-]+"></label>
      <label class="f">Role<select name="role">@foreach (\App\Models\User::ROLES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></label></div>
    <label class="f">Password <span id="uPw" class="dim"></span><input type="password" name="password" minlength="6" autocomplete="new-password"></label>
    <input type="hidden" name="aktif" value="0"><label class="chk"><input type="checkbox" name="aktif" value="1" checked> Aktif</label></div>
  <div class="dlg-f"><button class="btn acc block">Simpan</button></div></form></dialog>
@push('scripts')<script>
  function isi(u) { var f = document.querySelector('#dUser form'); f.id.value = u.id || ''; f.name.value = u.name || ''; f.username.value = u.username || ''; f.role.value = u.role || 'kasir';
    f.aktif.checked = u.aktif !== false; f.password.required = !u.id; document.getElementById('uPw').textContent = u.id ? '(kosongkan jika tidak diganti)' : '';
    document.getElementById('uT').textContent = u.id ? 'Edit pengguna' : 'Tambah pengguna'; document.getElementById('dUser').showModal(); }
</script>@endpush
@endsection
