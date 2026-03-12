<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'shipping_address',
        'shipping_address_id',
        'payment_method',
        'payment_method_code',
        'message',
        'total_amount',
        'discount_total',
        'voucher_code',
        'admin_fee',
        'service_fee',
        'seller_amount',
        'status',
        'payment_proof',
        'shipping_proof',
        'courier',
        'tracking_number',
        'shipped_at',
        'received_at',
        'funds_released_at',
        'receipt_photos',
        // New fields
        'delivery_type',
        'shipping_cost',
        'shipping_discount',
        'expires_at',
        'receipt_confirmed_at',
        'seller_notes',
        'user_hidden',
        'buyer_seen',
        'seller_seen',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
        'funds_released_at' => 'datetime',
        'expires_at' => 'datetime',
        'receipt_confirmed_at' => 'datetime',
        'receipt_photos' => 'array',
        'total_amount' => 'double',
        'discount_total' => 'double',
        'seller_amount' => 'double',
        'service_fee' => 'double',
        'shipping_cost' => 'double',
        'buyer_seen' => 'boolean',
        'seller_seen' => 'boolean',
    ];

    // Relationships
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function shippingAddressRecord()
    {
        return $this->belongsTo(UserAddress::class, 'shipping_address_id');
    }

    public function trackingLogs()
    {
        return $this->hasMany(OrderTrackingLog::class)->orderBy('created_at', 'asc');
    }

    // Helper to get primary product (fallback for views expecting single product)
    public function getProductAttribute()
    {
        return $this->items->first()->product ?? null;
    }

    // Helper to get tracking URL
    public function getTrackingUrlAttribute()
    {
        if (!$this->courier || !$this->tracking_number) {
            return null;
        }

        $urls = [
            'jne' => 'https://www.jne.co.id/id/tracking/trace',
            'jnt' => 'https://www.jet.co.id/track',
            'j&t' => 'https://www.jet.co.id/track',
            'sicepat' => 'https://www.sicepat.com/checkAwb',
            'anteraja' => 'https://anteraja.id/tracking',
            'pos' => 'https://www.posindonesia.co.id/id/tracking',
            'tiki' => 'https://www.tiki.id/id/tracking',
        ];

        return $urls[strtolower($this->courier)] ?? null;
    }

    public function getSubtotalAttribute()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->price * $item->quantity;
        }
        return $total;
    }
}
