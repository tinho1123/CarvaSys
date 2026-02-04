<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'company_id',
        'name',
        'surname',
        'email',
        'document_type',
        'document_number',
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    public function companies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Company::class, 'client_company')
            ->withPivot('is_active')
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ClientUser::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
