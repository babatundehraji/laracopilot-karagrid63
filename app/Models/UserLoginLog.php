<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'success',
        'logged_in_at'
    ];

    protected $casts = [
        'success' => 'boolean',
        'logged_in_at' => 'datetime'
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            if (empty($log->logged_in_at)) {
                $log->logged_in_at = now();
            }
            if (empty($log->ip_address)) {
                $log->ip_address = request()->ip();
            }
            if (empty($log->user_agent)) {
                $log->user_agent = request()->userAgent();
            }
        });
    }

    // Accessors
    public function getIsSuccessfulAttribute()
    {
        return $this->success === true;
    }

    public function getIsFailedAttribute()
    {
        return $this->success === false;
    }

    public function getStatusAttribute()
    {
        return $this->success ? 'Success' : 'Failed';
    }

    public function getStatusColorAttribute()
    {
        return $this->success ? 'green' : 'red';
    }

    public function getBrowserAttribute()
    {
        if (!$this->user_agent) {
            return 'Unknown';
        }

        $userAgent = $this->user_agent;

        if (preg_match('/Edge\/([0-9.]+)/', $userAgent)) {
            return 'Microsoft Edge';
        } elseif (preg_match('/Chrome\/([0-9.]+)/', $userAgent)) {
            return 'Google Chrome';
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent)) {
            return 'Mozilla Firefox';
        } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent) && !preg_match('/Chrome/', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/Opera\/([0-9.]+)/', $userAgent) || preg_match('/OPR\/([0-9.]+)/', $userAgent)) {
            return 'Opera';
        }

        return 'Unknown Browser';
    }

    public function getDeviceTypeAttribute()
    {
        if (!$this->user_agent) {
            return 'Unknown';
        }

        $userAgent = strtolower($this->user_agent);

        if (preg_match('/mobile|android|iphone|ipad|ipod|blackberry|iemobile|opera mini/', $userAgent)) {
            if (preg_match('/ipad/', $userAgent)) {
                return 'Tablet';
            }
            return 'Mobile';
        }

        return 'Desktop';
    }

    public function getOperatingSystemAttribute()
    {
        if (!$this->user_agent) {
            return 'Unknown';
        }

        $userAgent = $this->user_agent;

        if (preg_match('/Windows NT 10/', $userAgent)) {
            return 'Windows 10';
        } elseif (preg_match('/Windows NT 6.3/', $userAgent)) {
            return 'Windows 8.1';
        } elseif (preg_match('/Windows NT 6.2/', $userAgent)) {
            return 'Windows 8';
        } elseif (preg_match('/Windows NT 6.1/', $userAgent)) {
            return 'Windows 7';
        } elseif (preg_match('/Windows/', $userAgent)) {
            return 'Windows';
        } elseif (preg_match('/Mac OS X/', $userAgent)) {
            return 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            return 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            return 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/', $userAgent)) {
            return 'iOS';
        }

        return 'Unknown OS';
    }

    public function getLocationAttribute()
    {
        // Placeholder - would integrate with IP geolocation service
        if (!$this->ip_address) {
            return 'Unknown';
        }

        // Return IP for now, in production would use MaxMind, IPStack, etc.
        return $this->ip_address;
    }

    public function getTimeAgoAttribute()
    {
        return $this->logged_in_at->diffForHumans();
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeByIp($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('logged_in_at', now()->toDateString());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('logged_in_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('logged_in_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('logged_in_at', [$startDate, $endDate]);
    }

    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderBy('logged_in_at', 'desc')->limit($limit);
    }

    public function scopeSuspiciousActivity($query)
    {
        // Failed attempts from same IP within last hour
        return $query->failed()
            ->where('logged_in_at', '>=', now()->subHour())
            ->selectRaw('ip_address, COUNT(*) as attempt_count')
            ->groupBy('ip_address')
            ->having('attempt_count', '>=', 3);
    }

    public function scopeWithRelations($query)
    {
        return $query->with('user');
    }

    // Static helper methods
    public static function logAttempt($email, $success = false, $userId = null)
    {
        return self::create([
            'user_id' => $userId,
            'email' => $email,
            'success' => $success,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'logged_in_at' => now()
        ]);
    }

    public static function getFailedAttemptsCount($email, $minutes = 15)
    {
        return self::where('email', $email)
            ->where('success', false)
            ->where('logged_in_at', '>=', now()->subMinutes($minutes))
            ->count();
    }
}