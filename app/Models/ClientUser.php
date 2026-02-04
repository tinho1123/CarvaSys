<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel\Concerns\HasTenancy;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class ClientUser extends Authenticatable implements FilamentUser, HasTenants, MustVerifyEmail
{
    use HasFactory, HasTenancy, Notifiable;

    protected $fillable = [
        'uuid',
        'client_id',
        'email',
        'password',
        'document_type',
        'document_number',
        'remember_token',
        'last_login_at',
        'login_attempts',
        'locked_until',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'preferences' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function companies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Company::class, 'client_company', 'client_id', 'company_id')
            ->withPivot('is_active')
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getTenants(\Filament\Panel $panel): Collection
    {
        return $this->companies()->get();
    }

    public function canAccessTenant(\Illuminate\Database\Eloquent\Model $tenant): bool
    {
        return $this->companies()->where('companies.id', $tenant->id)->exists();
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $panel->getId() === 'client-admin';
    }

    public function getName(): string
    {
        return $this->email ?? 'Client User';
    }

    public function getUserName(): string
    {
        // Garante que sempre retorna string
        if (!empty($this->name)) {
            return (string) $this->name;
        }
        if (!empty($this->email)) {
            return (string) $this->email;
        }
        if (!empty($this->document_number)) {
            return (string) $this->document_number;
        }
        return 'Cliente';
    }

    public function getFilamentName(): string
    {
        return $this->getName();
    }
}
