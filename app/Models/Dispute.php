<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $fillable = [
        'transaction_id', 'buyer_id', 'seller_id',
        'reason', 'description', 'evidence_photos',
        'status', 'winner',
        'resolved_by', 'admin_notes',
        'admin_reviewed_at', 'buyer_shipped_back_at',
        'seller_received_back_at', 'refunded_at', 'resolved_at',
        'return_tracking_number', 'return_courier',
        'conversation_with_user_id',
    ];

    protected $casts = [
        'evidence_photos' => 'array',
        'admin_reviewed_at' => 'datetime',
        'buyer_shipped_back_at' => 'datetime',
        'seller_received_back_at' => 'datetime',
        'refunded_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function logs()
    {
        return $this->hasMany(DisputeLog::class)->orderBy('created_at', 'asc');
    }

    /** Helper: catat log dispute */
    public function addLog(string $actor, ?int $actorId, string $action, string $notes = '', array $metadata = [])
    {
        DisputeLog::create([
            'dispute_id'     => $this->id,
            'transaction_id' => $this->transaction_id,
            'actor'          => $actor,
            'actor_id'       => $actorId,
            'action'         => $action,
            'notes'          => $notes,
            'metadata'       => $metadata ?: null,
        ]);
    }
}
