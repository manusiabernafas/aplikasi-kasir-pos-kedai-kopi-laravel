<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    protected $casts = ['dibuka_at' => 'datetime', 'ditutup_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function orders() { return $this->hasMany(Order::class); }

    /** Ringkasan uang: tunai yang seharusnya ada di laci = modal + penjualan tunai */
    public function ringkasan(): array
    {
        $o = $this->orders()->where('status_bayar', 'lunas')->selectRaw('metode, COUNT(*) n, SUM(total) t')->groupBy('metode')->get()->keyBy('metode');
        $tunai = (int) ($o['tunai']->t ?? 0);
        return [
            'transaksi' => (int) $o->sum('n'),
            'tunai' => $tunai,
            'qris' => (int) ($o['qris']->t ?? 0),
            'debit' => (int) ($o['debit']->t ?? 0),
            'total' => (int) $o->sum('t'),
            'harus_ada' => $this->modal_awal + $tunai,
            'selisih' => $this->uang_fisik === null ? null : $this->uang_fisik - ($this->modal_awal + $tunai),
        ];
    }
}
