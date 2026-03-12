<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Transaction;

class NewOrderReceived extends Notification
{
    use Queueable;

    protected $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Pesanan Baru! ' . $this->transaction->quantity . 'x ' . $this->transaction->product->name,
            'transaction_id' => $this->transaction->id,
            'url' => route('transactions.history'), // Seller view could be different, but history works
        ];
    }
}
