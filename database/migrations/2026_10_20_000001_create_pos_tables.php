<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $t) {
            $t->string('kunci', 50)->primary();
            $t->text('nilai')->nullable();
        });

        Schema::create('kategori', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 50);
            $t->unsignedSmallInteger('urut')->default(0);
            $t->timestamps();
        });

        Schema::create('menu', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            $t->string('nama', 80);
            $t->string('deskripsi', 200)->nullable();
            $t->unsignedInteger('harga');
            $t->string('foto')->nullable();
            $t->boolean('tersedia')->default(true);
            $t->boolean('ke_dapur')->default(true);   // false = langsung siap (mis. air mineral)
            $t->unsignedSmallInteger('urut')->default(0);
            $t->timestamps();
        });

        // Grup opsi (Ukuran, Suhu, Gula, ...) yang bisa dipasang ke banyak menu
        Schema::create('opsi_grup', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 50);
            $t->boolean('wajib')->default(true);     // harus pilih satu
            $t->boolean('multi')->default(false);    // boleh pilih lebih dari satu (topping)
            $t->unsignedSmallInteger('urut')->default(0);
            $t->timestamps();
        });
        Schema::create('opsi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opsi_grup_id')->constrained('opsi_grup')->cascadeOnDelete();
            $t->string('nama', 50);
            $t->unsignedInteger('tambahan')->default(0);
            $t->unsignedSmallInteger('urut')->default(0);
        });
        Schema::create('menu_opsi_grup', function (Blueprint $t) {
            $t->foreignId('menu_id')->constrained('menu')->cascadeOnDelete();
            $t->foreignId('opsi_grup_id')->constrained('opsi_grup')->cascadeOnDelete();
            $t->primary(['menu_id', 'opsi_grup_id']);
        });

        Schema::create('shifts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users');
            $t->dateTime('dibuka_at');
            $t->unsignedInteger('modal_awal')->default(0);
            $t->dateTime('ditutup_at')->nullable();
            $t->unsignedInteger('uang_fisik')->nullable();
            $t->string('catatan', 200)->nullable();
            $t->index(['user_id', 'ditutup_at']);
        });

        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal');
            $t->unsignedSmallInteger('nomor');            // nomor antrean harian
            $t->foreignId('shift_id')->nullable()->constrained('shifts');
            $t->foreignId('user_id')->constrained('users');
            $t->enum('tipe', ['dine_in', 'takeaway'])->default('dine_in');
            $t->string('meja', 10)->nullable();
            $t->string('pelanggan', 60)->nullable();
            $t->unsignedInteger('subtotal');
            $t->unsignedInteger('diskon')->default(0);
            $t->unsignedInteger('pajak')->default(0);
            $t->unsignedInteger('total');
            $t->enum('status_bayar', ['lunas', 'belum', 'batal'])->default('lunas');
            $t->enum('metode', ['tunai', 'qris', 'debit'])->nullable();
            $t->unsignedInteger('dibayar')->default(0);
            $t->unsignedInteger('kembalian')->default(0);
            $t->dateTime('dibayar_at')->nullable();
            $t->enum('status_dapur', ['baru', 'dibuat', 'siap', 'diambil'])->default('baru');
            $t->dateTime('mulai_at')->nullable();
            $t->dateTime('siap_at')->nullable();
            $t->dateTime('diambil_at')->nullable();
            $t->string('catatan', 200)->nullable();
            $t->string('alasan_batal', 200)->nullable();
            $t->timestamps();
            $t->unique(['tanggal', 'nomor']);
            $t->index(['status_dapur', 'tanggal']);
            $t->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $t->foreignId('menu_id')->nullable()->constrained('menu')->nullOnDelete();
            $t->string('nama', 80);
            $t->string('opsi', 200)->nullable();     // contoh: "Large, Dingin, Gula sedikit"
            $t->unsignedInteger('harga');            // harga satuan termasuk tambahan opsi
            $t->unsignedSmallInteger('qty');
            $t->string('catatan', 120)->nullable();
        });
    }

    public function down(): void
    {
        foreach (['order_items', 'orders', 'shifts', 'menu_opsi_grup', 'opsi', 'opsi_grup', 'menu', 'kategori', 'pengaturan'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
