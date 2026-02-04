<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderEdit extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'edited_by_vendor_id',
        'old_data',
        'new_data',
        'status',
        'responded_by_user_id',
        'responded_at'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'responded_at' => 'datetime'
    ];

    // Accessors
    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    public function getIsAcceptedAttribute()
    {
        return $this->status === 'accepted';
    }

    public function getIsRejectedAttribute()
    {
        return $this->status === 'rejected';
    }

    public function getChangesAttribute()
    {
        $changes = [];
        
        foreach ($this->new_data as $key => $newValue) {
            $oldValue = $this->old_data[$key] ?? null;
            
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }
        
        return $changes;
    }

    public function getChangeSummaryAttribute()
    {
        $changes = $this->changes;
        $summary = [];
        
        foreach ($changes as $field => $change) {
            $fieldName = ucwords(str_replace('_', ' ', $field));
            $summary[] = "{$fieldName}: {$change['old']} → {$change['new']}";
        }
        
        return implode(', ', $summary);
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function editedByVendor()
    {
        return $this->belongsTo(Vendor::class, 'edited_by_vendor_id');
    }

    public function respondedByUser()
    {
        return $this->belongsTo(User::class, 'responded_by_user_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('edited_by_vendor_id', $vendorId);
    }

    public function scopeAwaitingResponse($query)
    {
        return $query->where('status', 'pending')
                     ->whereNull('responded_at');
    }

    public function scopeResponded($query)
    {
        return $query->whereNotNull('responded_at');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days))
                     ->orderBy('created_at', 'desc');
    }

    public function scopeWithRelations($query)
    {
        return $query->with([
            'order.customer',
            'editedByVendor.user',
            'respondedByUser'
        ]);
    }
}