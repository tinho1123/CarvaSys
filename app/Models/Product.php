<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'amount',
        'discounts',
        'total_amount',
        'quantity',
        'image',
        'isCool',
        'is_for_favored',
        'favored_price',
        'category_id',
        'active',
        'uuid',
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\ProductsCategories::class, 'category_id');
    }

    public function productsCategories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ProductsCategories::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Transaction::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
