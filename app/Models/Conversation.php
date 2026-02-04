<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'started_by_user_id',
        'last_message_at'
    ];

    protected $casts = [
        'last_message_at' => 'datetime'
    ];

    // Accessors
    public function getHasMessagesAttribute()
    {
        return !is_null($this->last_message_at);
    }

    public function getUnreadCountAttribute()
    {
        return $this->messages()->where('is_read', false)->count();
    }

    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    public function getParticipantsAttribute()
    {
        $participants = collect([
            $this->order->customer,
            $this->order->vendor->user
        ])->unique('id');

        return $participants;
    }

    public function getIsActiveAttribute()
    {
        // Active if last message was within 7 days
        if (!$this->last_message_at) {
            return false;
        }
        return $this->last_message_at->isAfter(now()->subDays(7));
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function startedBy()
    {
        return $this->belongsTo(User::class, 'started_by_user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Scopes
    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('order', function($q) use ($userId) {
            $q->where('customer_id', $userId)
              ->orWhereHas('vendor', function($vq) use ($userId) {
                  $vq->where('user_id', $userId);
              });
        });
    }

    public function scopeWithUnreadMessages($query, $userId)
    {
        return $query->whereHas('messages', function($q) use ($userId) {
            $q->where('is_read', false)
              ->where('sender_id', '!=', $userId);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('last_message_at', '>=', now()->subDays(7));
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('last_message_at', 'desc');
    }

    public function scopeWithRelations($query)
    {
        return $query->with([
            'order.service',
            'order.customer',
            'order.vendor.user',
            'startedBy',
            'messages' => function($q) {
                $q->latest()->limit(1);
            }
        ]);
    }

    // Helper methods
    public function markAsRead($userId)
    {
        $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }

    public function getUnreadCountForUser($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }
}