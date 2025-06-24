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
        'name',
        'foundation_date'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class, 'companies_users');
    }

    public function transactions(): HasMany
    {
        return  $this->hasMany(\App\Models\Transaction::class);
    }

    public function productsCategories(): HasMany
    {
        return $this->hasMany(\App\Models\ProductsCategories::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(\App\Models\Product::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(\App\Models\Client::class);
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
