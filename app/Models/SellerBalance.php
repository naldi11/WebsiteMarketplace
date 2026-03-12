<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerBalance extends Model
{
    protected $fillable = [
        'user_id',
        'available_balance',
        'pending_balance',
        'total_withdrawn',
        'total_earnings',
    ];

    protected $casts = [
        'available_balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'total_earnings' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Add to pending balance (when transaction created)
    public function addPending($amount)
    {
        $this->pending_balance += $amount;
        $this->save();
    }

    // Release from pending to available (when buyer confirms)
    public function releaseFunds($amount)
    {
        $this->pending_balance -= $amount;
        $this->available_balance += $amount;
        $this->total_earnings += $amount;
        $this->save();
    }

    // Withdraw from available
    public function withdraw($amount)
    {
        if ($amount > $this->available_balance) {
            throw new \Exception('Saldo tidak mencukupi');
        }
        $this->available_balance -= $amount;
        $this->total_withdrawn += $amount;
        $this->save();
    }

    // Get or create balance for user
    public static function getOrCreate($userId)
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'available_balance' => 0,
                'pending_balance' => 0,
                'total_withdrawn' => 0,
                'total_earnings' => 0,
            ]
        );
    }
}
