<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];
    protected $casts = ['tanggal' => 'date', 'dibayar_at' => 'datetime', 'mulai_at' => 'datetime', 'siap_at' => 'datetime', 'diambil_at' => 'datetime'];

    public const DAPUR = ['baru' => 'Baru', 'dibuat' => 'Dibuat', 'siap' => 'Siap', 'diambil' => 'Diambil'];
    public const METODE = ['tunai' => 'Tunai', 'qris' => 'QRIS', 'debit' => 'Debit'];

    public function items() { return $this->hasMany(OrderItem::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function shift() { return $this->belongsTo(Shift::class); }

    /** Nomor antrean yang dipanggil, contoh A-012 untuk dine in, B-012 untuk takeaway */
    public function label(): string
    {
        return ($this->tipe === 'takeaway' ? 'B' : 'A') . '-' . str_pad((string) $this->nomor, 3, '0', STR_PAD_LEFT);
    }
}
