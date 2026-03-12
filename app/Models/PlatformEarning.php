<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformEarning extends Model
{
    protected $fillable = [
        'transaction_id',
        'service_fee',
        'payment_fee',
        'description',
    ];

    protected $casts = [
        'service_fee' => 'decimal:2',
        'payment_fee' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Total earning dari transaksi ini
    public function getTotalAttribute()
    {
        return $this->service_fee + $this->payment_fee;
    }

    // Create earning record
    public static function recordEarning($transactionId, $serviceFee, $paymentFee = 0, $description = null)
    {
        return self::create([
            'transaction_id' => $transactionId,
            'service_fee' => $serviceFee,
            'payment_fee' => $paymentFee,
            'description' => $description ?? 'Service fee dari transaksi',
        ]);
    }
}
