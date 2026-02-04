<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Boot method for auto-generating slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && !$category->isDirty('slug')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Accessors
    public function getHasSubcategoriesAttribute()
    {
        return $this->subcategories()->count() > 0;
    }

    public function getActiveSubcategoriesCountAttribute()
    {
        return $this->subcategories()->active()->count();
    }

    public function getServicesCountAttribute()
    {
        return $this->services()->count();
    }

    // Relationships
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    public function scopeWithActiveSubcategories($query)
    {
        return $query->with(['subcategories' => function($q) {
            $q->active()->ordered();
        }]);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('slug', 'like', "%{$term}%");
        });
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->withCount('services')
                     ->orderBy('services_count', 'desc')
                     ->limit($limit);
    }
}