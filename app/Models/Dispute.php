<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_id',
        'vendor_id',
        'opened_by_user_id',
        'reason_code',
        'reason',
        'status',
        'resolution',
        'resolution_notes',
        'opened_at',
        'closed_at'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime'
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dispute) {
            if (empty($dispute->opened_at)) {
                $dispute->opened_at = now();
            }
        });
    }

    // Accessors
    public function getIsOpenAttribute()
    {
        return $this->status === 'open';
    }

    public function getIsUnderReviewAttribute()
    {
        return $this->status === 'under_review';
    }

    public function getIsResolvedAttribute()
    {
        return $this->status === 'resolved';
    }

    public function getIsClosedAttribute()
    {
        return $this->status === 'closed';
    }

    public function getIsActiveAttribute()
    {
        return in_array($this->status, ['open', 'under_review']);
    }

    public function getHasResolutionAttribute()
    {
        return !is_null($this->resolution);
    }

    public function getReasonCodeLabelAttribute()
    {
        $labels = [
            'no_show' => 'Vendor No-Show',
            'poor_quality' => 'Poor Service Quality',
            'incomplete' => 'Incomplete Service',
            'late_arrival' => 'Late Arrival',
            'unprofessional' => 'Unprofessional Behavior',
            'price_discrepancy' => 'Price Discrepancy',
            'damaged_property' => 'Damaged Property',
            'other' => 'Other Issue'
        ];

        return $labels[$this->reason_code] ?? ucwords(str_replace('_', ' ', $this->reason_code ?? 'Unknown'));
    }

    public function getResolutionLabelAttribute()
    {
        $labels = [
            'refund_customer' => 'Full Refund to Customer',
            'release_vendor' => 'Payment Released to Vendor',
            'partial' => 'Partial Refund',
            'none' => 'No Action Taken'
        ];

        return $labels[$this->resolution] ?? 'Pending';
    }

    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function getDaysOpenAttribute()
    {
        if ($this->closed_at) {
            return $this->opened_at->diffInDays($this->closed_at);
        }
        return $this->opened_at->diffInDays(now());
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by_user_id');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['open', 'under_review']);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeByReasonCode($query, $reasonCode)
    {
        return $query->where('reason_code', $reasonCode);
    }

    public function scopeByResolution($query, $resolution)
    {
        return $query->where('resolution', $resolution);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('opened_at', '>=', now()->subDays($days))
                     ->orderBy('opened_at', 'desc');
    }

    public function scopeOldest($query)
    {
        return $query->orderBy('opened_at', 'asc');
    }

    public function scopeWithRelations($query)
    {
        return $query->with([
            'order.service',
            'customer',
            'vendor.user',
            'openedBy'
        ]);
    }

    public function scopeRequiringAttention($query)
    {
        return $query->active()
                     ->where('opened_at', '<=', now()->subDays(3))
                     ->orderBy('opened_at', 'asc');
    }
}