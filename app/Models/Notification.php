<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'data',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    // Accessors
    public function getIsUnreadAttribute()
    {
        return !$this->is_read;
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'order' => 'Order Update',
            'system' => 'System Notification',
            'vendor' => 'Vendor Message',
            'admin' => 'Admin Notice',
            'payment' => 'Payment Update',
            'dispute' => 'Dispute Update',
            'message' => 'New Message',
            'promotion' => 'Promotion',
            'review' => 'Review Request'
        ];

        return $labels[$this->type] ?? ucfirst($this->type);
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getIsTodayAttribute()
    {
        return $this->created_at->isToday();
    }

    public function getIsRecentAttribute()
    {
        return $this->created_at->isAfter(now()->subDay());
    }

    public function getIconAttribute()
    {
        $icons = [
            'order' => '📦',
            'system' => '⚙️',
            'vendor' => '👤',
            'admin' => '👨‍💼',
            'payment' => '💳',
            'dispute' => '⚠️',
            'message' => '💬',
            'promotion' => '🎉',
            'review' => '⭐'
        ];

        return $icons[$this->type] ?? '🔔';
    }

    public function getActionUrlAttribute()
    {
        if (!$this->data) {
            return null;
        }

        // Generate appropriate URLs based on type and data
        switch ($this->type) {
            case 'order':
                return $this->data['order_id'] ?? null ? '/orders/' . $this->data['order_id'] : null;
            case 'message':
                return $this->data['conversation_id'] ?? null ? '/messages/' . $this->data['conversation_id'] : null;
            case 'dispute':
                return $this->data['dispute_id'] ?? null ? '/disputes/' . $this->data['dispute_id'] : null;
            case 'payment':
                return $this->data['transaction_id'] ?? null ? '/transactions/' . $this->data['transaction_id'] : null;
            default:
                return $this->data['url'] ?? null;
        }
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrders($query)
    {
        return $query->where('type', 'order');
    }

    public function scopeSystem($query)
    {
        return $query->where('type', 'system');
    }

    public function scopeVendor($query)
    {
        return $query->where('type', 'vendor');
    }

    public function scopeAdmin($query)
    {
        return $query->where('type', 'admin');
    }

    public function scopePayment($query)
    {
        return $query->where('type', 'payment');
    }

    public function scopeDispute($query)
    {
        return $query->where('type', 'dispute');
    }

    public function scopeMessage($query)
    {
        return $query->where('type', 'message');
    }

    public function scopePromotion($query)
    {
        return $query->where('type', 'promotion');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days))
                     ->orderBy('created_at', 'desc');
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Helper methods
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }
    }

    public static function createForUser($userId, $title, $body, $type, $data = null)
    {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'data' => $data
        ]);
    }
}