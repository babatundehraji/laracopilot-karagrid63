<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'state_id',
        'name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Accessors
    public function getFullLocationAttribute()
    {
        return "{$this->name}, {$this->state->name}, {$this->state->country->name}";
    }

    public function getShortLocationAttribute()
    {
        return "{$this->name}, {$this->state->name}";
    }

    // Relationships
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function country()
    {
        return $this->hasOneThrough(Country::class, State::class, 'id', 'id', 'state_id', 'country_id');
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

    public function scopeByState($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    public function scopeByCountry($query, $countryId)
    {
        return $query->whereHas('state', function($q) use ($countryId) {
            $q->where('country_id', $countryId);
        });
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%");
    }

    public function scopeWithLocation($query)
    {
        return $query->with(['state.country']);
    }
}