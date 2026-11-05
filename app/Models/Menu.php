<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $guarded = [];
    protected $casts = ['tersedia' => 'boolean', 'ke_dapur' => 'boolean', 'harga' => 'integer'];

    public function kategori() { return $this->belongsTo(Kategori::class); }
    public function opsiGrup() { return $this->belongsToMany(OpsiGrup::class, 'menu_opsi_grup')->orderBy('urut'); }

    public function fotoUrl(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }
}
