<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        "uuid",
        "company_id",
        "product_id",
        "fees_id",
        "name",
        "description",
        "amount",
        "discounts",
        "fees",
        "active",
        "total_amount",
        "quantity",
        "image",
        "isCool",
        "category_name",
        "category_id",
        "client_name",
        "client_id"
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function product():  \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function fee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Fee::class);
    }
}
