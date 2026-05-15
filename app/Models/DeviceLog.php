<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceLog extends Model
{
    protected $fillable = [
        'device_unique_id',
        'first_user_id',
        'is_new_user_claimed'
    ];

    protected $casts = [
        'is_new_user_claimed' => 'boolean'
    ];

    public function firstUser()
    {
        return $this->belongsTo(User::class, 'first_user_id');
    }
}
