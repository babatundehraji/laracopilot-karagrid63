<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'user_id',
        'type',
        'code',
        'expires_at',
        'used_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getIsExpiredAttribute()
    {
        return $this->expires_at->isPast();
    }

    public function getIsUsedAttribute()
    {
        return !is_null($this->used_at);
    }

    public function getIsValidAttribute()
    {
        return !$this->is_expired && !$this->is_used;
    }

    public function getExpiresInMinutesAttribute()
    {
        if ($this->is_expired) {
            return 0;
        }
        return now()->diffInMinutes($this->expires_at);
    }

    public function getTimeUntilExpiryAttribute()
    {
        if ($this->is_expired) {
            return 'Expired';
        }
        return $this->expires_at->diffForHumans();
    }

    // Scopes
    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now())
            ->whereNull('used_at');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeUsed($query)
    {
        return $query->whereNotNull('used_at');
    }

    public function scopeUnused($query)
    {
        return $query->whereNull('used_at');
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    // Static helper methods
    public static function generate($email, $type, $userId = null, $expiryMinutes = 15)
    {
        // Invalidate old codes for this email and type
        self::where('email', $email)
            ->where('type', $type)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        // Generate new 6-digit code
        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        return self::create([
            'email' => $email,
            'user_id' => $userId,
            'type' => $type,
            'code' => $code,
            'expires_at' => now()->addMinutes($expiryMinutes)
        ]);
    }

    public static function verify($email, $code, $type)
    {
        $verification = self::where('email', $email)
            ->where('code', $code)
            ->where('type', $type)
            ->valid()
            ->first();

        if (!$verification) {
            return false;
        }

        $verification->markAsUsed();
        return true;
    }

    public static function findValidCode($email, $code, $type)
    {
        return self::where('email', $email)
            ->where('code', $code)
            ->where('type', $type)
            ->valid()
            ->first();
    }

    public static function cleanupExpired($daysOld = 7)
    {
        return self::where('created_at', '<', now()->subDays($daysOld))
            ->where(function($q) {
                $q->whereNotNull('used_at')
                  ->orWhere('expires_at', '<', now());
            })
            ->delete();
    }

    // Instance methods
    public function markAsUsed()
    {
        $this->update(['used_at' => now()]);
    }

    public function isValidFor($email)
    {
        return $this->email === $email && $this->is_valid;
    }

    public function extend($minutes = 15)
    {
        if (!$this->is_used) {
            $this->update([
                'expires_at' => now()->addMinutes($minutes)
            ]);
        }
    }
}