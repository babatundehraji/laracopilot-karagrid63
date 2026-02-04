<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'image_url',
        'cta_label',
        'cta_url',
        'placement',
        'is_active',
        'starts_at',
        'ends_at',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime'
    ];

    // Accessors
    public function getIsCurrentlyActiveAttribute()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        // If starts_at is set, check if it's started
        if ($this->starts_at && $now->isBefore($this->starts_at)) {
            return false;
        }

        // If ends_at is set, check if it hasn't ended
        if ($this->ends_at && $now->isAfter($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function getHasCtaAttribute()
    {
        return !is_null($this->cta_label) && !is_null($this->cta_url);
    }

    public function getIsScheduledAttribute()
    {
        return !is_null($this->starts_at) || !is_null($this->ends_at);
    }

    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        $now = now();

        if ($this->starts_at && $now->isBefore($this->starts_at)) {
            return 'scheduled';
        }

        if ($this->ends_at && $now->isAfter($this->ends_at)) {
            return 'expired';
        }

        return 'active';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'scheduled' => 'Scheduled',
            'expired' => 'Expired'
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function getDaysRemainingAttribute()
    {
        if (!$this->ends_at) {
            return null;
        }

        $days = now()->diffInDays($this->ends_at, false);
        return $days > 0 ? $days : 0;
    }

    // Relationships
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
            ->where(function($q) use ($now) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', $now);
            });
    }

    public function scopeByPlacement($query, $placement)
    {
        return $query->where('placement', $placement);
    }

    public function scopeHome($query)
    {
        return $query->where('placement', 'home');
    }

    public function scopeDiscover($query)
    {
        return $query->where('placement', 'discover');
    }

    public function scopeScheduled($query)
    {
        return $query->where('starts_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<', now());
    }

    public function scopeWithCta($query)
    {
        return $query->whereNotNull('cta_label')
                     ->whereNotNull('cta_url');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}