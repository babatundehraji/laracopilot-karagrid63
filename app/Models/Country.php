<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iso2',
        'iso3',
        'phone_code',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->iso2 ? "{$this->name} ({$this->iso2})" : $this->name;
    }

    public function getFormattedPhoneCodeAttribute()
    {
        return $this->phone_code ? ltrim($this->phone_code, '+') : null;
    }

    // Relationships
    public function states()
    {
        return $this->hasMany(State::class);
    }

    public function cities()
    {
        return $this->hasManyThrough(City::class, State::class);
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
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

    public function scopeByIso2($query, $iso2)
    {
        return $query->where('iso2', strtoupper($iso2));
    }

    public function scopeByIso3($query, $iso3)
    {
        return $query->where('iso3', strtoupper($iso3));
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('iso2', 'like', "%{$term}%")
              ->orWhere('iso3', 'like', "%{$term}%");
        });
    }
}