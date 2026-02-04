<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'business_type',
        'tax_id',
        'registration_number',
        'website_url',
        'support_email',
        'support_phone',
        'country_id',
        'state_id',
        'city_id',
        'address_line1',
        'address_line2',
        'postal_code',
        'documents',
        'payout_method',
        'payout_currency',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_routing_number',
        'bank_swift_code',
        'paypal_email',
        'stripe_account_id',
        'rating',
        'total_reviews',
        'is_verified',
        'is_active',
        'verified_at'
    ];

    protected $casts = [
        'documents' => 'array',
        'rating' => 'decimal:2',
        'total_reviews' => 'integer',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'verified_at' => 'datetime'
    ];

    // Accessors
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city?->name,
            $this->state?->name,
            $this->postal_code,
            $this->country?->name
        ]);
        
        return implode(', ', $parts);
    }

    public function getPayoutDetailsAttribute()
    {
        $details = [
            'method' => $this->payout_method,
            'currency' => $this->payout_currency
        ];

        switch ($this->payout_method) {
            case 'bank_transfer':
                $details['bank_name'] = $this->bank_name;
                $details['account_name'] = $this->bank_account_name;
                $details['account_number'] = $this->bank_account_number;
                $details['routing_number'] = $this->bank_routing_number;
                $details['swift_code'] = $this->bank_swift_code;
                break;
            case 'paypal':
                $details['paypal_email'] = $this->paypal_email;
                break;
            case 'stripe':
                $details['stripe_account_id'] = $this->stripe_account_id;
                break;
        }

        return $details;
    }

    public function getHasCompletePayoutInfoAttribute()
    {
        switch ($this->payout_method) {
            case 'bank_transfer':
                return !empty($this->bank_name) && 
                       !empty($this->bank_account_name) && 
                       !empty($this->bank_account_number);
            case 'paypal':
                return !empty($this->paypal_email);
            case 'stripe':
                return !empty($this->stripe_account_id);
            case 'manual':
                return true;
            default:
                return false;
        }
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeWithCompletePayoutInfo($query)
    {
        return $query->where(function($q) {
            $q->where(function($q) {
                // Bank transfer
                $q->where('payout_method', 'bank_transfer')
                  ->whereNotNull('bank_name')
                  ->whereNotNull('bank_account_name')
                  ->whereNotNull('bank_account_number');
            })->orWhere(function($q) {
                // PayPal
                $q->where('payout_method', 'paypal')
                  ->whereNotNull('paypal_email');
            })->orWhere(function($q) {
                // Stripe
                $q->where('payout_method', 'stripe')
                  ->whereNotNull('stripe_account_id');
            })->orWhere('payout_method', 'manual');
        });
    }

    public function scopeByCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    public function scopeByState($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    public function scopeByCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeTopRated($query, $minRating = 4.0)
    {
        return $query->where('rating', '>=', $minRating)
                     ->where('total_reviews', '>', 0)
                     ->orderBy('rating', 'desc');
    }
}