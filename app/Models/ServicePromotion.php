<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'type',
        'label',
        'original_price',
        'promo_price',
        'starts_at',
        'ends_at',
        'priority',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'original_price' => 'decimal:2',
        'promo_price' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'priority' => 'integer',
        'is_active' => 'boolean'
    ];

    // Accessors
    public function getIsCurrentlyActiveAttribute()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        return $now->isBetween($this->starts_at, $this->ends_at);
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->original_price || !$this->promo_price) {
            return 0;
        }

        if ($this->original_price <= 0) {
            return 0;
        }

        $discount = (($this->original_price - $this->promo_price) / $this->original_price) * 100;
        return round($discount, 0);
    }

    public function getSavingsAttribute()
    {
        if (!$this->original_price || !$this->promo_price) {
            return 0;
        }

        return $this->original_price - $this->promo_price;
    }

    public function getFormattedOriginalPriceAttribute()
    {
        return '₦' . number_format($this->original_price, 2);
    }

    public function getFormattedPromoPriceAttribute()
    {
        return '₦' . number_format($this->promo_price, 2);
    }

    public function getFormattedSavingsAttribute()
    {
        return '₦' . number_format($this->savings, 2);
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'sponsored' => 'Sponsored',
            'featured' => 'Featured',
            'deal' => 'Special Deal'
        ];

        return $labels[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        $now = now();

        if ($now->isBefore($this->starts_at)) {
            return 'scheduled';
        }

        if ($now->isAfter($this->ends_at)) {
            return 'expired';
        }

        return 'active';
    }

    public function getDaysRemainingAttribute()
    {
        if (!$this->is_currently_active) {
            return 0;
        }

        return now()->diffInDays($this->ends_at, false);
    }

    public function getHoursRemainingAttribute()
    {
        if (!$this->is_currently_active) {
            return 0;
        }

        return now()->diffInHours($this->ends_at, false);
    }

    // Relationships
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeCurrentlyActive($query)
    {
        $now = now();
        
        return $query->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSponsored($query)
    {
        return $query->where('type', 'sponsored');
    }

    public function scopeFeatured($query)
    {
        return $query->where('type', 'featured');
    }

    public function scopeDeals($query)
    {
        return $query->where('type', 'deal');
    }

    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeScheduled($query)
    {
        return $query->where('starts_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<', now());
    }

    public function scopeEndingSoon($query, $hours = 24)
    {
        return $query->currentlyActive()
            ->where('ends_at', '<=', now()->addHours($hours))
            ->orderBy('ends_at', 'asc');
    }

    public function scopeHighPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    public function scopeWithRelations($query)
    {
        return $query->with([
            'service.vendor.user',
            'service.category',
            'service.subcategory',
            'creator'
        ]);
    }
}