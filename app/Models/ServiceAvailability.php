<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ServiceAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'date',
        'start_time',
        'end_time',
        'is_available'
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean'
    ];

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->date->format('l, F j, Y');
    }

    public function getTimeRangeAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return Carbon::parse($this->start_time)->format('g:i A') . ' - ' . Carbon::parse($this->end_time)->format('g:i A');
        }
        return 'All day';
    }

    public function getIsFullDayAttribute()
    {
        return is_null($this->start_time) && is_null($this->end_time);
    }

    public function getIsPastAttribute()
    {
        return $this->date->isPast();
    }

    public function getIsTodayAttribute()
    {
        return $this->date->isToday();
    }

    public function getIsFutureAttribute()
    {
        return $this->date->isFuture();
    }

    // Relationships
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeUnavailable($query)
    {
        return $query->where('is_available', false);
    }

    public function scopeForService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
                     ->orderBy('date', 'asc');
    }

    public function scopePast($query)
    {
        return $query->where('date', '<', now()->toDateString())
                     ->orderBy('date', 'desc');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', now()->toDateString());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            now()->startOfWeek()->toDateString(),
            now()->endOfWeek()->toDateString()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('date', [
            now()->startOfMonth()->toDateString(),
            now()->endOfMonth()->toDateString()
        ]);
    }

    public function scopeWithTimeSlot($query)
    {
        return $query->whereNotNull('start_time')
                     ->whereNotNull('end_time');
    }

    public function scopeFullDay($query)
    {
        return $query->whereNull('start_time')
                     ->whereNull('end_time');
    }
}