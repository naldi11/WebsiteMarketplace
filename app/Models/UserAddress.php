<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone',
        'full_address',
        'province',
        'city',
        'district',
        'postal_code',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Labels yang tersedia
    public static $labels = [
        'rumah' => 'Rumah',
        'kantor' => 'Kantor',
        'sekolah' => 'Sekolah',
        'kos' => 'Kos',
        'apartemen' => 'Apartemen',
        'lainnya' => 'Lainnya',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Format alamat lengkap
    public function getFormattedAddressAttribute()
    {
        $parts = array_filter([
            $this->full_address,
            $this->district,
            $this->city,
            $this->province,
            $this->postal_code
        ]);
        return implode(', ', $parts);
    }

    // Label dengan icon
    public function getLabelIconAttribute()
    {
        $icons = [
            'rumah' => '🏠',
            'kantor' => '🏢',
            'sekolah' => '🏫',
            'kos' => '🏘️',
            'apartemen' => '🏬',
            'lainnya' => '📍',
        ];
        return $icons[strtolower($this->label)] ?? '📍';
    }
}
