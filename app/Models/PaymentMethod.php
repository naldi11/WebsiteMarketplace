<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'code',
        'name',
        'account_number',
        'type',
        'icon',
        'instructions',
        'admin_fee',
        'admin_fee_percent',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'admin_fee' => 'decimal:2',
        'admin_fee_percent' => 'decimal:2',
    ];

    // Type icons
    public static $typeIcons = [
        'bank_transfer' => '🏦',
        'ewallet' => '📱',
        'qris' => '📷',
        'credit_card' => '💳',
        'cod' => '💵',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Calculate fee based on amount
    public function calculateFee($amount)
    {
        $percentFee = ($amount * $this->admin_fee_percent) / 100;
        return $this->admin_fee + $percentFee;
    }

    // Get type label
    public function getTypeLabelAttribute()
    {
        $labels = [
            'bank_transfer' => 'Transfer Bank',
            'ewallet' => 'E-Wallet',
            'qris' => 'QRIS',
            'credit_card' => 'Kartu Kredit',
            'cod' => 'Bayar di Tempat',
        ];
        return $labels[$this->type] ?? $this->type;
    }

    public function getTypeIconAttribute()
    {
        return self::$typeIcons[$this->type] ?? '💰';
    }
}
