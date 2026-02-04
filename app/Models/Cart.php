<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id'
    ];

    // Accessors
    public function getIsRecentAttribute()
    {
        return $this->created_at->isAfter(now()->subDays(7));
    }

    public function getDaysInCartAttribute()
    {
        return $this->created_at->diffInDays(now());
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getIsStaleAttribute()
    {
        // Items in cart for more than 30 days
        return $this->created_at->isBefore(now()->subDays(30));
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeStale($query, $days = 30)
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeWithRelations($query)
    {
        return $query->with([
            'user',
            'service.vendor.user',
            'service.category',
            'service.subcategory'
        ]);
    }

    public function scopeActiveServices($query)
    {
        return $query->whereHas('service', function($q) {
            $q->where('status', 'approved')
              ->where('is_active', true);
        });
    }

    // Static helper methods
    public static function addToCart($userId, $serviceId)
    {
        return self::firstOrCreate([
            'user_id' => $userId,
            'service_id' => $serviceId
        ]);
    }

    public static function removeFromCart($userId, $serviceId)
    {
        return self::where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->delete();
    }

    public static function clearCart($userId)
    {
        return self::where('user_id', $userId)->delete();
    }

    public static function getCartCount($userId)
    {
        return self::where('user_id', $userId)->count();
    }

    public static function getCartTotal($userId)
    {
        return self::where('user_id', $userId)
            ->with('service')
            ->get()
            ->sum(function($cart) {
                return $cart->service->price ?? 0;
            });
    }

    public static function isInCart($userId, $serviceId)
    {
        return self::where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->exists();
    }
}