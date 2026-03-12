<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionStatusLog extends Model
{
    protected $fillable = [
        'transaction_id',
        'status',
        'note',
        'changed_by',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
