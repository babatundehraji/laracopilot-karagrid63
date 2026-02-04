<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'subcategory_id',
        'title',
        'slug',
        'short_description',
        'description',
        'pricing_type',
        'price',
        'min_hours',
        'max_hours',
        'is_remote',
        'is_onsite',
        'service_country_id',
        'service_state_id',
        'service_city_id',
        'address_line1',
        'address_line2',
        'postal_code',
        'latitude',
        'longitude',
        'main_image_url',
        'gallery_images',
        'is_featured',
        'is_sponsored',
        'average_rating',
        'review_count',
        'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'min_hours' => 'integer',
        'max_hours' => 'integer',
        'is_remote' => 'boolean',
        'is_onsite' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'gallery_images' => 'array',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'average_rating' => 'decimal:2',
        'review_count' => 'integer'
    ];

    // Boot method for auto-generating slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->title);
                
                // Ensure unique slug
                $count = 1;
                $originalSlug = $service->slug;
                while (static::where('slug', $service->slug)->exists()) {
                    $service->slug = $originalSlug . '-' . $count++;
                }
            }
        });

        static::updating(function ($service) {
            if ($service->isDirty('title') && !$service->isDirty('slug')) {
                $service->slug = Str::slug($service->title);
                
                // Ensure unique slug
                $count = 1;
                $originalSlug = $service->slug;
                while (static::where('slug', $service->slug)->where('id', '!=', $service->id)->exists()) {
                    $service->slug = $originalSlug . '-' . $count++;
                }
            }
        });
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return '₦' . number_format($this->price, 2);
    }

    public function getPriceDescriptionAttribute()
    {
        if ($this->pricing_type === 'hourly') {
            return $this->formatted_price . '/hour';
        }
        return $this->formatted_price . ' (Fixed)';
    }

    public function getHourRangeAttribute()
    {
        if ($this->min_hours && $this->max_hours) {
            return "{$this->min_hours}-{$this->max_hours} hours";
        } elseif ($this->min_hours) {
            return "Min {$this->min_hours} hours";
        } elseif ($this->max_hours) {
            return "Max {$this->max_hours} hours";
        }
        return null;
    }

    public function getDeliveryMethodsAttribute()
    {
        $methods = [];
        if ($this->is_remote) $methods[] = 'Remote';
        if ($this->is_onsite) $methods[] = 'On-site';
        return implode(', ', $methods);
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->serviceCity?->name,
            $this->serviceState?->name,
            $this->postal_code,
            $this->serviceCountry?->name
        ]);
        
        return implode(', ', $parts);
    }

    public function getIsApprovedAttribute()
    {
        return $this->status === 'approved';
    }

    public function getHasLocationAttribute()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function serviceCountry()
    {
        return $this->belongsTo(Country::class, 'service_country_id');
    }

    public function serviceState()
    {
        return $this->belongsTo(State::class, 'service_state_id');
    }

    public function serviceCity()
    {
        return $this->belongsTo(City::class, 'service_city_id');
    }

    public function availabilities()
    {
        return $this->hasMany(ServiceAvailability::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function promotions()
    {
        return $this->hasMany(ServicePromotion::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeSponsored($query)
    {
        return $query->where('is_sponsored', true);
    }

    public function scopeRemote($query)
    {
        return $query->where('is_remote', true);
    }

    public function scopeOnsite($query)
    {
        return $query->where('is_onsite', true);
    }

    public function scopeHourly($query)
    {
        return $query->where('pricing_type', 'hourly');
    }

    public function scopeFixed($query)
    {
        return $query->where('pricing_type', 'fixed');
    }

    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeBySubcategory($query, $subcategoryId)
    {
        return $query->where('subcategory_id', $subcategoryId);
    }

    public function scopeByCountry($query, $countryId)
    {
        return $query->where('service_country_id', $countryId);
    }

    public function scopeByState($query, $stateId)
    {
        return $query->where('service_state_id', $stateId);
    }

    public function scopeByCity($query, $cityId)
    {
        return $query->where('service_city_id', $cityId);
    }

    public function scopePriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('price', [$minPrice, $maxPrice]);
    }

    public function scopeMinRating($query, $rating)
    {
        return $query->where('average_rating', '>=', $rating);
    }

    public function scopeTopRated($query, $limit = 10)
    {
        return $query->whereNotNull('average_rating')
                     ->where('review_count', '>', 0)
                     ->orderBy('average_rating', 'desc')
                     ->limit($limit);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('short_description', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('slug', 'like', "%{$term}%");
        });
    }

    public function scopeWithRelations($query)
    {
        return $query->with([
            'vendor.user',
            'category',
            'subcategory',
            'serviceCountry',
            'serviceState',
            'serviceCity'
        ]);
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->approved()
                     ->orderBy('review_count', 'desc')
                     ->orderBy('average_rating', 'desc')
                     ->limit($limit);
    }
}