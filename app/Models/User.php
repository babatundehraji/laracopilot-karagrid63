<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_code',
        'phone',
        'password',
        'avatar_url',
        'bio',
        'role',
        'status',
        'country',
        'state',
        'city',
        'timezone',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
        'notification_prefs',
        'meta'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'notification_prefs' => 'array',
        'meta' => 'array'
    ];

    // Accessors
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getInitialsAttribute()
    {
        $firstInitial = $this->first_name ? strtoupper($this->first_name[0]) : '';
        $lastInitial = $this->last_name ? strtoupper($this->last_name[0]) : '';
        return $firstInitial . $lastInitial;
    }

    public function getFullPhoneAttribute()
    {
        if (!$this->phone) {
            return null;
        }
        return $this->phone_code ? "{$this->phone_code}{$this->phone}" : $this->phone;
    }

    public function getIsEmailVerifiedAttribute()
    {
        return !is_null($this->email_verified_at);
    }

    public function getIsPhoneVerifiedAttribute()
    {
        return !is_null($this->phone_verified_at);
    }

    public function getIsFullyVerifiedAttribute()
    {
        return $this->is_email_verified && $this->is_phone_verified;
    }

    public function getIsAdminAttribute()
    {
        return $this->role === 'admin';
    }

    public function getIsVendorAttribute()
    {
        return $this->role === 'vendor';
    }

    public function getIsCustomerAttribute()
    {
        return $this->role === 'customer';
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getIsSuspendedAttribute()
    {
        return $this->status === 'suspended';
    }

    public function getHasAvatarAttribute()
    {
        return !is_null($this->avatar_url);
    }

    public function getAvatarAttribute()
    {
        if ($this->avatar_url) {
            return $this->avatar_url;
        }

        // Generate default avatar using UI Avatars
        $name = urlencode($this->full_name);
        return "https://ui-avatars.com/api/?name={$name}&size=200&background=6366f1&color=fff";
    }

    public function getFullLocationAttribute()
    {
        $parts = array_filter([$this->city, $this->state, $this->country]);
        return !empty($parts) ? implode(', ', $parts) : null;
    }

    public function getLastLoginAttribute()
    {
        return $this->last_login_at ? $this->last_login_at->diffForHumans() : 'Never';
    }

    public function getRoleLabelAttribute()
    {
        return ucfirst($this->role);
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }

    public function getNotificationPreference($key, $default = true)
    {
        if (!$this->notification_prefs) {
            return $default;
        }

        return $this->notification_prefs[$key] ?? $default;
    }

    public function getMeta($key, $default = null)
    {
        if (!$this->meta) {
            return $default;
        }

        return $this->meta[$key] ?? $default;
    }

    // Relationships
    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'customer_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function loginLogs()
    {
        return $this->hasMany(UserLoginLog::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    // Scopes
    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }

    public function scopeVendors($query)
    {
        return $query->where('role', 'vendor');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeUnverified($query)
    {
        return $query->whereNull('email_verified_at');
    }

    public function scopePhoneVerified($query)
    {
        return $query->whereNotNull('phone_verified_at');
    }

    public function scopeFullyVerified($query)
    {
        return $query->whereNotNull('email_verified_at')
            ->whereNotNull('phone_verified_at');
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeRecentlyRegistered($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeRecentlyActive($query, $days = 30)
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }

    public function scopeInactive($query, $days = 90)
    {
        return $query->where(function($q) use ($days) {
            $q->where('last_login_at', '<', now()->subDays($days))
              ->orWhereNull('last_login_at');
        });
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    public function scopeWithRelations($query)
    {
        return $query->with(['vendor', 'orders', 'notifications']);
    }

    // Helper methods
    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }

    public function suspend($reason = null)
    {
        $this->update([
            'status' => 'suspended',
            'meta' => array_merge($this->meta ?? [], [
                'suspension_reason' => $reason,
                'suspended_at' => now()->toDateTimeString()
            ])
        ]);
    }

    public function activate()
    {
        $this->update([
            'status' => 'active',
            'meta' => array_merge($this->meta ?? [], [
                'activated_at' => now()->toDateTimeString()
            ])
        ]);
    }

    public function setNotificationPreference($key, $value)
    {
        $prefs = $this->notification_prefs ?? [];
        $prefs[$key] = $value;
        $this->update(['notification_prefs' => $prefs]);
    }

    public function setMeta($key, $value)
    {
        $meta = $this->meta ?? [];
        $meta[$key] = $value;
        $this->update(['meta' => $meta]);
    }
}