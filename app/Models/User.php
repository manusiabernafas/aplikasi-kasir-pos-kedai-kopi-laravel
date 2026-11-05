<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'username', 'email', 'role', 'aktif', 'password'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'aktif' => 'boolean'];
    }

    public const ROLES = ['owner' => 'Pemilik', 'kasir' => 'Kasir', 'barista' => 'Barista'];

    public function punya(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /** Shift kasir yang masih terbuka (belum ditutup) */
    public function shiftAktif(): ?Shift
    {
        return Shift::where('user_id', $this->id)->whereNull('ditutup_at')->latest('dibuka_at')->first();
    }
}
