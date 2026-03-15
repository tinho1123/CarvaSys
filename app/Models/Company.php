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
        'description',
        'type',
        'logo_path',
        'banner_path',
        'foundation_date',
        'rating',
        'delivery_time',
        'is_promoted',
        'active',
    ];

    protected $casts = [
        'foundation_date' => 'date',
        'rating' => 'decimal:1',
        'active' => 'boolean',
        'is_promoted' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'companies_users');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function productsCategories(): HasMany
    {
        return $this->hasMany(ProductsCategories::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function favoredTransactions(): HasMany
    {
        return $this->hasMany(FavoredTransaction::class);
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
