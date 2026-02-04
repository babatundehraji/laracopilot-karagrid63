<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'category',
        'amount',
        'currency',
        'balance_after',
        'reference',
        'meta',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'meta' => 'array'
    ];

    // Accessors
    public function getFormattedAmountAttribute()
    {
        $symbol = $this->currency === 'NGN' ? '₦' : '$';
        $sign = $this->type === 'credit' ? '+' : '-';
        return $sign . $symbol . number_format($this->amount, 2);
    }

    public function getFormattedBalanceAfterAttribute()
    {
        if (is_null($this->balance_after)) {
            return null;
        }
        $symbol = $this->currency === 'NGN' ? '₦' : '$';
        return $symbol . number_format($this->balance_after, 2);
    }

    public function getIsCreditAttribute()
    {
        return $this->type === 'credit';
    }

    public function getIsDebitAttribute()
    {
        return $this->type === 'debit';
    }

    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    public function getIsReversedAttribute()
    {
        return $this->status === 'reversed';
    }

    public function getCategoryLabelAttribute()
    {
        return ucfirst($this->category);
    }

    public function getDescriptionAttribute()
    {
        $descriptions = [
            'order' => 'Payment for order',
            'earning' => 'Earnings from service',
            'promotion' => 'Service promotion fee',
            'payout' => 'Payout withdrawal',
            'refund' => 'Order refund',
            'fee' => 'Platform fee',
            'adjustment' => 'Balance adjustment'
        ];

        return $descriptions[$this->category] ?? ucfirst($this->category);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopeCredit($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebit($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrders($query)
    {
        return $query->where('category', 'order');
    }

    public function scopeEarnings($query)
    {
        return $query->where('category', 'earning');
    }

    public function scopePromotions($query)
    {
        return $query->where('category', 'promotion');
    }

    public function scopePayouts($query)
    {
        return $query->where('category', 'payout');
    }

    public function scopeRefunds($query)
    {
        return $query->where('category', 'refund');
    }

    public function scopeFees($query)
    {
        return $query->where('category', 'fee');
    }

    public function scopeAdjustments($query)
    {
        return $query->where('category', 'adjustment');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReversed($query)
    {
        return $query->where('status', 'reversed');
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

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeAmountRange($query, $minAmount, $maxAmount)
    {
        return $query->whereBetween('amount', [$minAmount, $maxAmount]);
    }

    public function scopeWithRelations($query)
    {
        return $query->with(['user', 'order.service']);
    }

    // Helper methods for balance calculation
    public static function getUserBalance($userId)
    {
        $credits = self::where('user_id', $userId)
            ->where('type', 'credit')
            ->completed()
            ->sum('amount');

        $debits = self::where('user_id', $userId)
            ->where('type', 'debit')
            ->completed()
            ->sum('amount');

        return $credits - $debits;
    }
}