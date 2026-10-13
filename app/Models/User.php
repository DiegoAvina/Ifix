<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lunar\Base\LunarUser;
use Lunar\Base\Traits\LunarUser as LunarUserTrait;

class User extends Authenticatable implements FilamentUser, LunarUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, LunarUserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Controla el acceso al panel Filament (/admin).
     *
     * `is_admin` nunca está en $fillable, así que no puede asignarse
     * por mass assignment desde ningún request (registro, perfil, etc.):
     * solo se activa a mano en base de datos o vía el comando
     * `php artisan admin:promote`.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin === true;
    }
}
