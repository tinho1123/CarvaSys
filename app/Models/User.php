<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Filament\Panel\Concerns\HasTenancy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasApiTokens, HasFactory, HasTenancy, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'uuid',
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

    protected static function booted(): void
    {
        static::creating(function (User $model) {
            if (is_null($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public function company(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'companies_users');
    }

    public function companies(): BelongsToMany
    {
        return $this->company();
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->companies()->get();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->companies()->wherePivot('company_id', $tenant->id)->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
