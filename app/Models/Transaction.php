<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (auth()->check() && ! $transaction->company_id) {
                // Para painel admin, obter empresa do usuário logado
                $user = auth()->user();
                if ($user instanceof \App\Models\User) {
                    $transaction->company_id = $user->companies->first()->id;
                }
            }
            if (! $transaction->uuid) {
                $transaction->uuid = \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $fillable = [
        'uuid',
        'company_id',
        'product_id',
        'fees_id',
        'name',
        'description',
        'amount',
        'discounts',
        'fees',
        'active',
        'total_amount',
        'quantity',
        'image',
        'isCool',
        'category_name',
        'category_id',
        'client_name',
        'client_id',
        'type',
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function fee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Fee::class);
    }

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Client::class, 'client_id');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
