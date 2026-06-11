<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutRecord extends Model
{
    protected $fillable = [
        'transaction_id',
        'seller_id',
        'amount',
        'bank_name',
        'account_number',
        'account_holder_name',
        'transfer_proof',
        'admin_id',
        'notes',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // ── Relations ────────────────────────────────────────────

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * Create a pending payout record.
     */
    public static function createPending(
        int $transactionId,
        int $sellerId,
        float $amount,
        string $bankName,
        string $accountNumber,
        string $accountHolderName,
        ?int $adminId = null,
        ?string $notes = null
    ): self {
        return self::create([
            'transaction_id'       => $transactionId,
            'seller_id'            => $sellerId,
            'amount'               => $amount,
            'bank_name'            => $bankName,
            'account_number'       => $accountNumber,
            'account_holder_name'  => $accountHolderName,
            'status'               => 'pending',
            'admin_id'             => $adminId,
            'notes'                => $notes,
        ]);
    }

    /**
     * Mark payout as completed with transfer proof.
     */
    public function markCompleted(string $transferProof, int $adminId, ?string $notes = null): self
    {
        $this->update([
            'transfer_proof' => $transferProof,
            'admin_id'       => $adminId,
            'status'         => 'completed',
            'paid_at'        => now(),
            'notes'          => $notes ?? $this->notes,
        ]);

        return $this;
    }
}
