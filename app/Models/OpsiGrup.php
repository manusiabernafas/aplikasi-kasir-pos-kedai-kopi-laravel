<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpsiGrup extends Model
{
    protected $table = 'opsi_grup';
    protected $guarded = [];
    protected $casts = ['wajib' => 'boolean', 'multi' => 'boolean'];

    public function opsi() { return $this->hasMany(Opsi::class)->orderBy('urut'); }
    public function menu() { return $this->belongsToMany(Menu::class, 'menu_opsi_grup'); }
}
