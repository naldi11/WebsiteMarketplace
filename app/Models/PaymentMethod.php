<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'icon',
        'is_active',
        'sort_order',
        'type', // 'manual', 'wallet', 'gateway'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
