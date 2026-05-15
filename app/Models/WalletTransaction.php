<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'reference_type',
        'reference_id',
        'description',
        'balance_after',
        'status',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
