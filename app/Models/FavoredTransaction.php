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
        'due_date',
        'category_name',
        'category_id',
        'client_name',
        'order_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discounts' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'favored_total' => 'decimal:2',
        'favored_paid_amount' => 'decimal:2',
        'quantity' => 'integer',
        'active' => 'boolean',
        'due_date' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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

    protected static function booted(): void
    {
        static::creating(function (FavoredTransaction $model) {
            // If amount or total_amount are not provided, default them
            // to the favored_total when available, or to 0.
            if (is_null($model->amount)) {
                $model->amount = $model->favored_total ?? 0;
            }

            if (is_null($model->total_amount)) {
                $model->total_amount = $model->favored_total ?? $model->amount ?? 0;
            }

            if (is_null($model->favored_total)) {
                $model->favored_total = $model->total_amount ?? $model->amount ?? 0;
            }

            if (is_null($model->favored_paid_amount)) {
                $model->favored_paid_amount = 0;
            }
        });
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
