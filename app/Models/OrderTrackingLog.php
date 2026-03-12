<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTrackingLog extends Model
{
    protected $fillable = [
        'transaction_id',
        'status',
        'title',
        'description',
        'actor_type',
        'actor_id',
    ];

    // Status constants with their display info
    public static $statuses = [
        'order_created' => [
            'title' => 'Pesanan Dibuat',
            'description' => 'Pesanan berhasil dibuat, menunggu pembayaran',
            'icon' => '🛒',
            'color' => 'gray',
        ],
        'payment_uploaded' => [
            'title' => 'Bukti Pembayaran Diunggah',
            'description' => 'Pembeli telah mengunggah bukti pembayaran',
            'icon' => '💳',
            'color' => 'yellow',
        ],
        'payment_verified' => [
            'title' => 'Pembayaran Diverifikasi',
            'description' => 'Admin telah memverifikasi pembayaran',
            'icon' => '✅',
            'color' => 'green',
        ],
        'processing' => [
            'title' => 'Pesanan Diproses',
            'description' => 'Penjual sedang memproses pesanan Anda',
            'icon' => '📦',
            'color' => 'blue',
        ],
        'packaging' => [
            'title' => 'Sedang Dikemas',
            'description' => 'Pesanan Anda sedang dikemas oleh penjual',
            'icon' => '📦',
            'color' => 'blue',
        ],
        'ready_to_ship' => [
            'title' => 'Siap Dikirim',
            'description' => 'Paket siap diserahkan ke kurir',
            'icon' => '✨',
            'color' => 'blue',
        ],
        'handed_to_courier' => [
            'title' => 'Diserahkan ke Kurir',
            'description' => 'Paket telah diserahkan ke jasa pengiriman',
            'icon' => '🚚',
            'color' => 'purple',
        ],
        'in_transit' => [
            'title' => 'Dalam Pengiriman',
            'description' => 'Paket sedang dalam perjalanan',
            'icon' => '🚚',
            'color' => 'purple',
        ],
        'out_for_delivery' => [
            'title' => 'Dalam Pengantaran',
            'description' => 'Paket sedang diantar ke alamat Anda',
            'icon' => '📍',
            'color' => 'purple',
        ],
        'delivered' => [
            'title' => 'Terkirim',
            'description' => 'Paket telah sampai di tujuan',
            'icon' => '📬',
            'color' => 'teal',
        ],
        'received' => [
            'title' => 'Diterima Pembeli',
            'description' => 'Pembeli telah mengkonfirmasi penerimaan barang',
            'icon' => '✅',
            'color' => 'teal',
        ],
        'completed' => [
            'title' => 'Selesai',
            'description' => 'Transaksi selesai, dana diteruskan ke penjual',
            'icon' => '🎉',
            'color' => 'green',
        ],
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    // Get status info
    public function getStatusInfoAttribute()
    {
        return self::$statuses[$this->status] ?? [
            'title' => $this->title,
            'description' => $this->description,
            'icon' => '📋',
            'color' => 'gray',
        ];
    }

    // Static method to add tracking log
    public static function addLog($transactionId, $status, $customTitle = null, $customDesc = null, $actorType = 'system', $actorId = null)
    {
        $statusInfo = self::$statuses[$status] ?? ['title' => $status, 'description' => ''];

        return self::create([
            'transaction_id' => $transactionId,
            'status' => $status,
            'title' => $customTitle ?? $statusInfo['title'],
            'description' => $customDesc ?? $statusInfo['description'],
            'actor_type' => $actorType,
            'actor_id' => $actorId,
        ]);
    }
}
