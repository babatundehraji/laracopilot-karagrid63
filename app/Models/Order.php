<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_reference',
        'customer_id',
        'vendor_id',
        'service_id',
        'service_title',
        'service_pricing_type',
        'service_price',
        'status',
        'service_date',
        'start_time',
        'hours',
        'end_time',
        'location_type',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'currency',
        'subtotal',
        'discount_amount',
        'platform_fee',
        'tax_amount',
        'total_amount',
        'payment_status',
        'paid_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'disputed_at',
        'customer_note',
        'vendor_note'
    ];

    protected $casts = [
        'service_date' => 'date',
        'service_price' => 'decimal:2',
        'hours' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'disputed_at' => 'datetime'
    ];

    // Boot method for auto-generating order reference
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_reference)) {
                $order->order_reference = 'ORD-' . strtoupper(uniqid());
            }
        });
    }

    // Accessors
    public function getFormattedTotalAttribute()
    {
        return '₦' . number_format($this->total_amount, 2);
    }

    public function getFormattedSubtotalAttribute()
    {
        return '₦' . number_format($this->subtotal, 2);
    }

    public function getFormattedServiceDateAttribute()
    {
        return $this->service_date->format('l, F j, Y');
    }

    public function getTimeRangeAttribute()
    {
        $start = Carbon::parse($this->start_time)->format('g:i A');
        if ($this->end_time) {
            $end = Carbon::parse($this->end_time)->format('g:i A');
            return "{$start} - {$end}";
        }
        if ($this->hours) {
            return "{$start} ({$this->hours} hours)";
        }
        return $start;
    }

    public function getFullAddressAttribute()
    {
        if ($this->location_type === 'remote') {
            return 'Remote Service';
        }
        
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->country
        ]);
        
        return implode(', ', $parts);
    }

    public function getIsPaidAttribute()
    {
        return $this->payment_status === 'paid';
    }

    public function getIsActiveAttribute()
    {
        return in_array($this->status, ['pending', 'edited', 'active']);
    }

    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    public function getIsDisputedAttribute()
    {
        return $this->status === 'disputed';
    }

    public function getIsCancelledAttribute()
    {
        return $this->status === 'cancelled';
    }

    public function getCanBeEditedAttribute()
    {
        return in_array($this->status, ['pending', 'edited']);
    }

    public function getCanBeCancelledAttribute()
    {
        return in_array($this->status, ['pending', 'edited', 'active']);
    }

    public function getHasLocationAttribute()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    public function getDaysUntilServiceAttribute()
    {
        return now()->diffInDays($this->service_date, false);
    }

    // Relationships
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function edits()
    {
        return $this->hasMany(OrderEdit::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function dispute()
    {
        return $this->hasOne(Dispute::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeEdited($query)
    {
        return $query->where('status', 'edited');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', 'disputed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeRemote($query)
    {
        return $query->where('location_type', 'remote');
    }

    public function scopeOnsite($query)
    {
        return $query->where('location_type', 'onsite');
    }

    public function scopeServiceDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('service_date', [$startDate, $endDate]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('service_date', '>=', now()->toDateString())
                     ->orderBy('service_date', 'asc')
                     ->orderBy('start_time', 'asc');
    }

    public function scopePast($query)
    {
        return $query->where('service_date', '<', now()->toDateString())
                     ->orderBy('service_date', 'desc');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('service_date', now()->toDateString());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('service_date', [
            now()->startOfWeek()->toDateString(),
            now()->endOfWeek()->toDateString()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('service_date', [
            now()->startOfMonth()->toDateString(),
            now()->endOfMonth()->toDateString()
        ]);
    }

    public function scopeWithRelations($query)
    {
        return $query->with([
            'customer',
            'vendor.user',
            'service.category',
            'service.subcategory'
        ]);
    }

    public function scopeRecentFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeHighValue($query, $minAmount = 50000)
    {
        return $query->where('total_amount', '>=', $minAmount);
    }
}