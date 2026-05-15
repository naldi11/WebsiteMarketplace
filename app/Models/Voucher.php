<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'name',
        'discount_type',
        'discount_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_count',
        'quota_total',
        'min_purchase',
        'start_date',
        'end_date',
        'target_user_id',
        'category_id',
        'is_active',
        'terms',
        'description'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function isValidFor($amount, $userId = null, $itemCategoryIds = [])
    {
        if (!$this->is_active)
            return false;
        
        $now = now();
        if ($this->start_date && $now->lt($this->start_date))
            return false;
        if ($this->end_date && $now->gt($this->end_date))
            return false;

        if ($this->usage_count >= $this->usage_limit)
            return false;
        if ($amount < $this->min_purchase)
            return false;
        if ($this->target_user_id && $this->target_user_id !== $userId)
            return false;
        
        // Category Restriction logic
        if ($this->category_id) {
            if (empty($itemCategoryIds) || !in_array($this->category_id, $itemCategoryIds)) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscount($amount)
    {
        if ($this->discount_type === 'percent') {
            $discount = ($this->discount_amount / 100) * $amount;
            if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
                return (double) $this->max_discount_amount;
            }
            return (double) $discount;
        }

        // fixed
        return (double) min($this->discount_amount, $amount);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }
}
