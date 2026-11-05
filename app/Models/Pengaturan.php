<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';
    protected $primaryKey = 'kunci';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public const DEFAULT = [
        'nama_toko' => 'Kopi Pos', 'alamat' => '', 'telepon' => '', 'footer_struk' => 'Terima kasih, sampai jumpa lagi!',
        'pajak_persen' => '0', 'jumlah_meja' => '12', 'kode_layar' => '',
    ];

    public static function semua(): array
    {
        return Cache::rememberForever('pengaturan', fn () => array_merge(self::DEFAULT, self::pluck('nilai', 'kunci')->all()));
    }

    public static function ambil(string $k): string
    {
        return (string) (self::semua()[$k] ?? '');
    }

    public static function simpan(array $data): void
    {
        foreach ($data as $k => $v) self::updateOrCreate(['kunci' => $k], ['nilai' => $v]);
        Cache::forget('pengaturan');
    }
}
