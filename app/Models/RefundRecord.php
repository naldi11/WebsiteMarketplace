<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundRecord extends Model
{
    protected $fillable = [
        'transaction_id',
        'dispute_id',
        'buyer_id',
        'amount',
        'refund_method',
        'bank_name',
        'account_number',
        'account_holder_name',
        'transfer_proof',
        'admin_id',
        'notes',
        'status',
        'refunded_at',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'refunded_at' => 'datetime',
    ];

    // ── Relations ────────────────────────────────────────────

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function dispute()
    {
        return $this->belongsTo(Dispute::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * Create a pending refund record.
     */
    public static function createPending(
        int $transactionId,
        int $buyerId,
        float $amount,
        ?int $disputeId = null,
        ?int $adminId = null,
        ?string $notes = null
    ): self {
        return self::create([
            'transaction_id' => $transactionId,
            'dispute_id'     => $disputeId,
            'buyer_id'       => $buyerId,
            'amount'         => $amount,
            'refund_method'  => 'bank_transfer',
            'status'         => 'pending',
            'admin_id'       => $adminId,
            'notes'          => $notes,
        ]);
    }

    /**
     * Mark refund as completed with transfer proof.
     */
    public function markCompleted(string $transferProof, ?string $notes = null): self
    {
        $this->update([
            'transfer_proof' => $transferProof,
            'status'         => 'completed',
            'refunded_at'    => now(),
            'notes'          => $notes ?? $this->notes,
        ]);

        return $this;
    }
}
