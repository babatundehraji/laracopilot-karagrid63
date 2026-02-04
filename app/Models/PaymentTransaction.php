<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'provider',
        'provider_payment_id',
        'provider_charge_id',
        'amount',
        'currency',
        'status',
        'error_code',
        'error_message',
        'raw_request',
        'raw_response',
        'paid_at',
        'refunded_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_request' => 'array',
        'raw_response' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime'
    ];

    // Accessors
    public function getFormattedAmountAttribute()
    {
        $symbol = $this->currency === 'NGN' ? '₦' : '$';
        return $symbol . number_format($this->amount, 2);
    }

    public function getIsSuccessfulAttribute()
    {
        return $this->status === 'succeeded';
    }

    public function getIsFailedAttribute()
    {
        return $this->status === 'failed';
    }

    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    public function getIsRefundedAttribute()
    {
        return $this->status === 'refunded';
    }

    public function getHasErrorAttribute()
    {
        return !is_null($this->error_code) || !is_null($this->error_message);
    }

    public function getProviderNameAttribute()
    {
        return ucfirst($this->provider);
    }

    public function getProcessingTimeAttribute()
    {
        if ($this->paid_at) {
            return $this->created_at->diffInSeconds($this->paid_at) . ' seconds';
        }
        return null;
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSucceeded($query)
    {
        return $query->where('status', 'succeeded');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeWithErrors($query)
    {
        return $query->where(function($q) {
            $q->whereNotNull('error_code')
              ->orWhereNotNull('error_message');
        });
    }

    public function scopeAmountRange($query, $minAmount, $maxAmount)
    {
        return $query->whereBetween('amount', [$minAmount, $maxAmount]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days))
                     ->orderBy('created_at', 'desc');
    }

    public function scopeHighValue($query, $minAmount = 50000)
    {
        return $query->where('amount', '>=', $minAmount);
    }

    public function scopeWithRelations($query)
    {
        return $query->with(['order.service', 'user']);
    }
}