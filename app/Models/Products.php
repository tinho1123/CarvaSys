<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Products extends Model
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
        'category_id',
        'active',
        'uuid',
    ];

    public function company() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function category() {
        return $this->belongsTo(\App\Models\ProductsCategories::class,'category_id');
    }

    public function productsCategories() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Products::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
