<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            // Africa
            ['name' => 'Nigeria', 'iso2' => 'NG', 'iso3' => 'NGA', 'phone_code' => '+234', 'is_active' => true],
            ['name' => 'Ghana', 'iso2' => 'GH', 'iso3' => 'GHA', 'phone_code' => '+233', 'is_active' => true],
            ['name' => 'Kenya', 'iso2' => 'KE', 'iso3' => 'KEN', 'phone_code' => '+254', 'is_active' => true],
            ['name' => 'South Africa', 'iso2' => 'ZA', 'iso3' => 'ZAF', 'phone_code' => '+27', 'is_active' => true],
            ['name' => 'Egypt', 'iso2' => 'EG', 'iso3' => 'EGY', 'phone_code' => '+20', 'is_active' => true],
            
            // North America
            ['name' => 'United States', 'iso2' => 'US', 'iso3' => 'USA', 'phone_code' => '+1', 'is_active' => true],
            ['name' => 'Canada', 'iso2' => 'CA', 'iso3' => 'CAN', 'phone_code' => '+1', 'is_active' => true],
            ['name' => 'Mexico', 'iso2' => 'MX', 'iso3' => 'MEX', 'phone_code' => '+52', 'is_active' => true],
            
            // Europe
            ['name' => 'United Kingdom', 'iso2' => 'GB', 'iso3' => 'GBR', 'phone_code' => '+44', 'is_active' => true],
            ['name' => 'Germany', 'iso2' => 'DE', 'iso3' => 'DEU', 'phone_code' => '+49', 'is_active' => true],
            ['name' => 'France', 'iso2' => 'FR', 'iso3' => 'FRA', 'phone_code' => '+33', 'is_active' => true],
            ['name' => 'Spain', 'iso2' => 'ES', 'iso3' => 'ESP', 'phone_code' => '+34', 'is_active' => true],
            ['name' => 'Italy', 'iso2' => 'IT', 'iso3' => 'ITA', 'phone_code' => '+39', 'is_active' => true],
            
            // Asia
            ['name' => 'India', 'iso2' => 'IN', 'iso3' => 'IND', 'phone_code' => '+91', 'is_active' => true],
            ['name' => 'China', 'iso2' => 'CN', 'iso3' => 'CHN', 'phone_code' => '+86', 'is_active' => true],
            ['name' => 'Japan', 'iso2' => 'JP', 'iso3' => 'JPN', 'phone_code' => '+81', 'is_active' => true],
            ['name' => 'United Arab Emirates', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '+971', 'is_active' => true],
            ['name' => 'Singapore', 'iso2' => 'SG', 'iso3' => 'SGP', 'phone_code' => '+65', 'is_active' => true],
            
            // Oceania
            ['name' => 'Australia', 'iso2' => 'AU', 'iso3' => 'AUS', 'phone_code' => '+61', 'is_active' => true],
            ['name' => 'New Zealand', 'iso2' => 'NZ', 'iso3' => 'NZL', 'phone_code' => '+64', 'is_active' => true],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}