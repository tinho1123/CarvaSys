<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'foundation_date',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class, 'companies_users');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(\App\Models\Transaction::class);
    }

    public function productsCategories(): HasMany
    {
        return $this->hasMany(\App\Models\ProductsCategories::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(\App\Models\Product::class);
    }

    public function clients(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Client::class, 'client_company')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function fees(): HasMany
    {
        return $this->hasMany(\App\Models\Fee::class);
    }

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey(): int|string|null
    {
        return $this->id;
    }
}
