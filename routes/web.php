<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DapurController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LayarController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'form'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:10,1']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Layar TV "pesanan siap" (tanpa login, hanya menampilkan nomor & nama depan)
Route::get('/layar', [LayarController::class, 'index'])->name('layar');
Route::get('/layar/data', [LayarController::class, 'data'])->name('layar.data');

Route::middleware('auth')->group(function () {
    Route::get('/', [AuthController::class, 'home'])->name('home');

    Route::middleware('role:owner,kasir')->group(function () {
        Route::get('/kasir', [KasirController::class, 'index'])->name('kasir');
        Route::get('/kasir/menu', [KasirController::class, 'menu'])->name('kasir.menu');
        Route::post('/kasir/order', [KasirController::class, 'store'])->name('kasir.store');
        Route::get('/kasir/open-bill', [KasirController::class, 'openBills'])->name('kasir.open');
        Route::post('/kasir/order/{order}/bayar', [KasirController::class, 'bayar'])->name('kasir.bayar');
        Route::post('/kasir/order/{order}/batal', [KasirController::class, 'batal'])->name('kasir.batal');
        Route::get('/struk/{order}', [KasirController::class, 'struk'])->name('struk');

        Route::get('/shift', [ShiftController::class, 'index'])->name('shift');
        Route::post('/shift/buka', [ShiftController::class, 'buka'])->name('shift.buka');
        Route::post('/shift/tutup', [ShiftController::class, 'tutup'])->name('shift.tutup');
        Route::get('/shift/{shift}/cetak', [ShiftController::class, 'cetak'])->name('shift.cetak');

        Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat');
        Route::get('/riwayat/{order}', [RiwayatController::class, 'show'])->name('riwayat.show');
    });

    Route::middleware('role:owner,kasir,barista')->group(function () {
        Route::get('/dapur', [DapurController::class, 'index'])->name('dapur');
        Route::get('/dapur/data', [DapurController::class, 'data'])->name('dapur.data');
        Route::post('/dapur/{order}/status', [DapurController::class, 'status'])->name('dapur.status');
        Route::post('/dapur/menu/{menu}/tersedia', [DapurController::class, 'tersedia'])->name('dapur.tersedia');
    });

    Route::middleware('role:owner')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
        Route::get('/laporan/csv', [LaporanController::class, 'csv'])->name('laporan.csv');
        Route::get('/menu', [MenuController::class, 'index'])->name('menu');
        Route::post('/menu', [MenuController::class, 'simpan'])->name('menu.simpan');
        Route::delete('/menu/{menu}', [MenuController::class, 'hapus'])->name('menu.hapus');
        Route::post('/menu/kategori', [MenuController::class, 'kategori'])->name('menu.kategori');
        Route::post('/menu/opsi', [MenuController::class, 'opsi'])->name('menu.opsi');
        Route::get('/pengguna', [UserController::class, 'index'])->name('pengguna');
        Route::post('/pengguna', [UserController::class, 'simpan'])->name('pengguna.simpan');
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan');
        Route::post('/pengaturan', [PengaturanController::class, 'simpan'])->name('pengaturan.simpan');
    });
});
