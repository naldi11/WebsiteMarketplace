<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisputeLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'dispute_id', 'transaction_id',
        'actor', 'actor_id',
        'action', 'notes', 'metadata',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public function dispute()
    {
        return $this->belongsTo(Dispute::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
