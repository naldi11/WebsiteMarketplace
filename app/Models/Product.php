<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'description',
        'price',
        'discount_price',
        'stock',
        'image',
        'location',
        'latitude',
        'longitude',
        'condition',
        'weight',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
    ];

    protected $appends = ['has_discount', 'effective_price', 'discount_percent', 'avg_rating', 'review_count'];

    /**
     * Get average rating
     */
    public function getAvgRatingAttribute()
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    /**
     * Get review count
     */
    public function getReviewCountAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Check if product has an active discount
     */
    public function hasDiscount(): bool
    {
        return $this->discount_price !== null && $this->discount_price > 0 && $this->discount_price < $this->price;
    }

    /**
     * Get has_discount attribute for serialization
     */
    public function getHasDiscountAttribute(): bool
    {
        return $this->hasDiscount();
    }

    /**
     * Get effective price (discount price if available, otherwise regular price)
     */
    public function getEffectivePriceAttribute()
    {
        return $this->hasDiscount() ? $this->discount_price : $this->price;
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentAttribute()
    {
        if (!$this->hasDiscount())
            return 0;
        return round((($this->price - $this->discount_price) / $this->price) * 100);
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? false, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%');
        });

        $query->when($filters['category'] ?? false, function ($query, $category) {
            $query->whereHas('category', function ($query) use ($category) {
                $query->where('slug', $category);
            });
        });

        $query->when($filters['location'] ?? false, function ($query, $location) {
            $query->where('location', 'like', '%' . $location . '%');
        });
    }

    /**
     * Scope a query to sort products by distance from a given latitude and longitude.
     */
    public function scopeNearby($query, $latitude, $longitude)
    {
        // Haversine formula (distance in km)
        $haversine = "(6371 * acos(cos(radians($latitude)) 
                     * cos(radians(latitude)) 
                     * cos(radians(longitude) - radians($longitude)) 
                     + sin(radians($latitude)) 
                     * sin(radians(latitude))))";

        return $query->selectRaw("*, {$haversine} AS distance")
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('distance', 'asc');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
