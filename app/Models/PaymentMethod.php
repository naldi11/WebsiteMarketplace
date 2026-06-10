<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'account_number',
        'account_holder_name',
        'code',
        'description',
        'icon',
        'is_active',
        'sort_order',
        'type',
        'instructions',
        'admin_fee',
        'admin_fee_percent',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
