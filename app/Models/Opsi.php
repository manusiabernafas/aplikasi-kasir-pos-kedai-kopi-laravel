<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opsi extends Model
{
    protected $table = 'opsi';
    protected $guarded = [];
    public $timestamps = false;
    protected $casts = ['tambahan' => 'integer'];

    public function grup() { return $this->belongsTo(OpsiGrup::class, 'opsi_grup_id'); }
}
