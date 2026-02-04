<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'attachments',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::created(function ($message) {
            // Update conversation's last_message_at
            $message->conversation->update([
                'last_message_at' => $message->created_at
            ]);
        });
    }

    // Accessors
    public function getHasAttachmentsAttribute()
    {
        return !is_null($this->attachments) && count($this->attachments) > 0;
    }

    public function getAttachmentCountAttribute()
    {
        return $this->has_attachments ? count($this->attachments) : 0;
    }

    public function getIsUnreadAttribute()
    {
        return !$this->is_read;
    }

    public function getFormattedBodyAttribute()
    {
        return nl2br(e($this->body));
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getIsSentTodayAttribute()
    {
        return $this->created_at->isToday();
    }

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Scopes
    public function scopeByConversation($query, $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    public function scopeBySender($query, $senderId)
    {
        return $query->where('sender_id', $senderId);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeWithAttachments($query)
    {
        return $query->whereNotNull('attachments');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeWithRelations($query)
    {
        return $query->with(['conversation.order', 'sender']);
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
}