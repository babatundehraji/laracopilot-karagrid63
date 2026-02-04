<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceAvailability extends Model
{
    protected $fillable = [
        'service_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active'
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
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
     * Get day name from day_of_week number
     */
    public function getDayNameAttribute(): string
    {
        $days = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday'
        ];

        return $days[$this->day_of_week] ?? 'Unknown';
    }

    /**
     * Get short day name
     */
    public function getDayShortNameAttribute(): string
    {
        $days = [
            0 => 'Sun',
            1 => 'Mon',
            2 => 'Tue',
            3 => 'Wed',
            4 => 'Thu',
            5 => 'Fri',
            6 => 'Sat'
        ];

        return $days[$this->day_of_week] ?? 'N/A';
    }

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute(): string
    {
        return $this->start_time->format('g:i A') . ' - ' . $this->end_time->format('g:i A');
    }

    /**
     * Check if availability is currently active
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        $currentDayOfWeek = $now->dayOfWeek; // 0=Sunday ... 6=Saturday
        $currentTime = $now->format('H:i:s');

        return $this->day_of_week === $currentDayOfWeek
            && $currentTime >= $this->start_time->format('H:i:s')
            && $currentTime <= $this->end_time->format('H:i:s');
    }

    /**
     * Scope: Active availabilities only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by specific day
     */
    public function scopeForDay($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    /**
     * Scope: Order by day of week and start time
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('day_of_week')->orderBy('start_time');
    }
}