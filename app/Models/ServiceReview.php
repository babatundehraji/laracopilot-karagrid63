<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReview extends Model
{
    protected $fillable = [
        'service_id',
        'vendor_id',
        'user_id',
        'order_id',
        'rating',
        'comment',
        'status'
    ];

    protected $casts = [
        'rating' => 'integer',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationship: Service
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Relationship: Vendor
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Relationship: User (reviewer)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope: Only visible reviews
     */
    public function scopeVisible($query)
    {
        return $query->where('status', 'visible');
    }

    /**
     * Scope: Order by recent first
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get reviewer name
     */
    public function getReviewerNameAttribute(): string
    {
        return $this->user ? $this->user->full_name : 'Anonymous';
    }

    /**
     * Check if review is by authenticated user
     */
    public function isOwnedBy($userId): bool
    {
        return $this->user_id === $userId;
    }

    /**
     * Recalculate and update service rating
     */
    public static function recalculateServiceRating($serviceId): void
    {
        $service = Service::find($serviceId);

        if ($service) {
            $stats = self::where('service_id', $serviceId)
                ->where('status', 'visible')
                ->selectRaw('COUNT(*) as count, AVG(rating) as average')
                ->first();

            $service->update([
                'rating' => $stats->average ? round($stats->average, 1) : 0,
                'total_reviews' => $stats->count ?? 0
            ]);
        }
    }

    /**
     * Recalculate and update vendor rating
     */
    public static function recalculateVendorRating($vendorId): void
    {
        $vendor = Vendor::find($vendorId);

        if ($vendor) {
            $stats = self::where('vendor_id', $vendorId)
                ->where('status', 'visible')
                ->selectRaw('COUNT(*) as count, AVG(rating) as average')
                ->first();

            $vendor->update([
                'rating' => $stats->average ? round($stats->average, 1) : 0,
                'total_reviews' => $stats->count ?? 0
            ]);
        }
    }
}