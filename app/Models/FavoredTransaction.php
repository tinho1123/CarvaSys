<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoredTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'company_id',
        'client_id',
        'product_id',
        'name',
        'description',
        'amount',
        'discounts',
        'total_amount',
        'favored_total',
        'favored_paid_amount',
        'quantity',
        'image',
        'active',
        'category_name',
        'category_id',
        'client_name',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discounts' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'favored_total' => 'decimal:2',
        'favored_paid_amount' => 'decimal:2',
        'quantity' => 'integer',
        'active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductsCategories::class, 'category_id');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getRemainingBalance(): float
    {
        return $this->favored_total - $this->favored_paid_amount;
    }

    public function isFullyPaid(): bool
    {
        return $this->favored_paid_amount >= $this->favored_total;
    }
}
