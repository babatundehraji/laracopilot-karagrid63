<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent'
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            if (empty($log->ip_address)) {
                $log->ip_address = request()->ip();
            }
            if (empty($log->user_agent)) {
                $log->user_agent = request()->userAgent();
            }
        });
    }

    // Accessors
    public function getActionLabelAttribute()
    {
        $labels = [
            // Order actions
            'order_created' => 'Order Created',
            'order_updated' => 'Order Updated',
            'order_cancelled' => 'Order Cancelled',
            'order_completed' => 'Order Completed',
            'order_disputed' => 'Order Disputed',
            
            // Service actions
            'service_created' => 'Service Created',
            'service_updated' => 'Service Updated',
            'service_deleted' => 'Service Deleted',
            'service_approved' => 'Service Approved',
            'service_rejected' => 'Service Rejected',
            
            // Vendor actions
            'vendor_registered' => 'Vendor Registered',
            'vendor_verified' => 'Vendor Verified',
            'vendor_suspended' => 'Vendor Suspended',
            'vendor_reactivated' => 'Vendor Reactivated',
            
            // User actions
            'user_registered' => 'User Registered',
            'user_updated' => 'User Updated',
            'user_deleted' => 'User Deleted',
            'password_changed' => 'Password Changed',
            'email_verified' => 'Email Verified',
            
            // Payment actions
            'payment_initiated' => 'Payment Initiated',
            'payment_completed' => 'Payment Completed',
            'payment_failed' => 'Payment Failed',
            'payment_refunded' => 'Payment Refunded',
            
            // Dispute actions
            'dispute_opened' => 'Dispute Opened',
            'dispute_resolved' => 'Dispute Resolved',
            'dispute_closed' => 'Dispute Closed',
            
            // Admin actions
            'banner_created' => 'Banner Created',
            'promotion_created' => 'Promotion Created',
            'category_created' => 'Category Created',
            
            // System actions
            'system_maintenance' => 'System Maintenance',
            'data_export' => 'Data Export',
            'settings_updated' => 'Settings Updated'
        ];

        return $labels[$this->action] ?? ucwords(str_replace('_', ' ', $this->action));
    }

    public function getActionCategoryAttribute()
    {
        $categories = [
            'order' => ['order_created', 'order_updated', 'order_cancelled', 'order_completed', 'order_disputed'],
            'service' => ['service_created', 'service_updated', 'service_deleted', 'service_approved', 'service_rejected'],
            'vendor' => ['vendor_registered', 'vendor_verified', 'vendor_suspended', 'vendor_reactivated'],
            'user' => ['user_registered', 'user_updated', 'user_deleted', 'password_changed', 'email_verified'],
            'payment' => ['payment_initiated', 'payment_completed', 'payment_failed', 'payment_refunded'],
            'dispute' => ['dispute_opened', 'dispute_resolved', 'dispute_closed'],
            'admin' => ['banner_created', 'promotion_created', 'category_created'],
            'system' => ['system_maintenance', 'data_export', 'settings_updated']
        ];

        foreach ($categories as $category => $actions) {
            if (in_array($this->action, $actions)) {
                return $category;
            }
        }

        return 'other';
    }

    public function getIconAttribute()
    {
        $icons = [
            'order' => '📦',
            'service' => '🛠️',
            'vendor' => '👤',
            'user' => '👨‍💼',
            'payment' => '💳',
            'dispute' => '⚠️',
            'admin' => '⚙️',
            'system' => '🔧'
        ];

        return $icons[$this->action_category] ?? '📝';
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getHasSubjectAttribute()
    {
        return !is_null($this->subject_type) && !is_null($this->subject_id);
    }

    public function getSubjectLinkAttribute()
    {
        if (!$this->has_subject) {
            return null;
        }

        $routes = [
            'Order' => '/orders/',
            'Service' => '/services/',
            'Vendor' => '/vendors/',
            'User' => '/users/',
            'Dispute' => '/disputes/',
            'Banner' => '/banners/',
            'ServicePromotion' => '/promotions/'
        ];

        $baseRoute = $routes[$this->subject_type] ?? null;
        return $baseRoute ? $baseRoute . $this->subject_id : null;
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeBySubject($query, $subjectType, $subjectId = null)
    {
        $query->where('subject_type', $subjectType);
        
        if (!is_null($subjectId)) {
            $query->where('subject_id', $subjectId);
        }
        
        return $query;
    }

    public function scopeOrders($query)
    {
        return $query->where('subject_type', 'Order');
    }

    public function scopeServices($query)
    {
        return $query->where('subject_type', 'Service');
    }

    public function scopeVendors($query)
    {
        return $query->where('subject_type', 'Vendor');
    }

    public function scopeUsers($query)
    {
        return $query->where('subject_type', 'User');
    }

    public function scopePayments($query)
    {
        return $query->whereIn('action', [
            'payment_initiated',
            'payment_completed',
            'payment_failed',
            'payment_refunded'
        ]);
    }

    public function scopeDisputes($query)
    {
        return $query->whereIn('action', [
            'dispute_opened',
            'dispute_resolved',
            'dispute_closed'
        ]);
    }

    public function scopeAdminActions($query)
    {
        return $query->whereIn('action', [
            'service_approved',
            'service_rejected',
            'vendor_verified',
            'vendor_suspended',
            'banner_created',
            'promotion_created',
            'category_created'
        ]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeWithRelations($query)
    {
        return $query->with('user');
    }

    // Static helper methods
    public static function log($action, $description = null, $userId = null, $subjectType = null, $subjectId = null)
    {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    public static function logForModel($action, $model, $description = null, $userId = null)
    {
        return self::log(
            $action,
            $description,
            $userId,
            get_class($model),
            $model->id
        );
    }
}