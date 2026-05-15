<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_number',
        'balance',
        'pending_balance',
        'pin',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get or create wallet for user
     */
    public static function getOrCreate($userId)
    {
        $wallet = self::where('user_id', $userId)->first();
        if (!$wallet) {
            $wallet = self::create([
                'user_id' => $userId,
                'wallet_number' => 'MP' . str_pad($userId, 6, '0', STR_PAD_LEFT) . Str::upper(Str::random(4)),
                'balance' => 0,
                'pending_balance' => 0,
                'is_active' => true
            ]);
        }
        return $wallet;
    }

    /**
     * Add funds to balance (Top up / Refund)
     */
    public function credit($amount, $type, $description, $refType = null, $refId = null)
    {
        $this->balance += $amount;
        $this->save();

        return $this->transactions()->create([
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'balance_after' => $this->balance,
            'status' => 'success'
        ]);
    }

    /**
     * Deduct funds from balance (Payment)
     */
    public function debit($amount, $type, $description, $refType = null, $refId = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Saldo MeyPay tidak mencukupi');
        }

        $this->balance -= $amount;
        $this->save();

        return $this->transactions()->create([
            'amount' => -$amount,
            'type' => $type,
            'description' => $description,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'balance_after' => $this->balance,
            'status' => 'success'
        ]);
    }

    /**
     * Move funds to pending (Escrow)
     */
    public function moveToPending($amount)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Saldo tidak mencukupi untuk pembayaran escrow');
        }

        $this->balance -= $amount;
        $this->pending_balance += $amount;
        $this->save();
    }

    /**
     * Release funds from pending to another wallet (Complete order)
     */
    public function releaseTo(Wallet $targetWallet, $amount, $description, $refType = null, $refId = null)
    {
        if ($this->pending_balance < $amount) {
            throw new \Exception('Saldo tertahan tidak mencukupi');
        }

        $this->pending_balance -= $amount;
        $this->save();

        $targetWallet->credit($amount, 'payout', $description, $refType, $refId);
    }
}
