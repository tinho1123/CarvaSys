<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel\Concerns\HasTenancy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasApiTokens, HasFactory, Notifiable, HasTenancy;

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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function company(): BelongsToMany {
        return $this->belongsToMany(\App\Models\Company::class, 'companies_users');
    }

    public function getTenants(\Filament\Panel $panel): \Illuminate\Support\Collection
    {
        return $this->company()->get();
    }

    public function canAccessTenant(\Illuminate\Database\Eloquent\Model $tenant): bool
    {
        return $this->company()->whereKey($tenant)->exists();
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return true;
    }
}
