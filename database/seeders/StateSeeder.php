<?php

namespace Database\Seeders;

use App\Models\State;
use App\Models\Country;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $nigeria = Country::where('iso2', 'NG')->first();
        $us = Country::where('iso2', 'US')->first();
        $gb = Country::where('iso2', 'GB')->first();
        $ca = Country::where('iso2', 'CA')->first();
        $gh = Country::where('iso2', 'GH')->first();

        // Nigerian states (36 states + FCT)
        if ($nigeria) {
            $nigerianStates = [
                ['name' => 'Abia', 'code' => 'AB'],
                ['name' => 'Adamawa', 'code' => 'AD'],
                ['name' => 'Akwa Ibom', 'code' => 'AK'],
                ['name' => 'Anambra', 'code' => 'AN'],
                ['name' => 'Bauchi', 'code' => 'BA'],
                ['name' => 'Bayelsa', 'code' => 'BY'],
                ['name' => 'Benue', 'code' => 'BE'],
                ['name' => 'Borno', 'code' => 'BO'],
                ['name' => 'Cross River', 'code' => 'CR'],
                ['name' => 'Delta', 'code' => 'DE'],
                ['name' => 'Ebonyi', 'code' => 'EB'],
                ['name' => 'Edo', 'code' => 'ED'],
                ['name' => 'Ekiti', 'code' => 'EK'],
                ['name' => 'Enugu', 'code' => 'EN'],
                ['name' => 'Federal Capital Territory', 'code' => 'FC'],
                ['name' => 'Gombe', 'code' => 'GO'],
                ['name' => 'Imo', 'code' => 'IM'],
                ['name' => 'Jigawa', 'code' => 'JI'],
                ['name' => 'Kaduna', 'code' => 'KD'],
                ['name' => 'Kano', 'code' => 'KN'],
                ['name' => 'Katsina', 'code' => 'KT'],
                ['name' => 'Kebbi', 'code' => 'KE'],
                ['name' => 'Kogi', 'code' => 'KO'],
                ['name' => 'Kwara', 'code' => 'KW'],
                ['name' => 'Lagos', 'code' => 'LA'],
                ['name' => 'Nasarawa', 'code' => 'NA'],
                ['name' => 'Niger', 'code' => 'NI'],
                ['name' => 'Ogun', 'code' => 'OG'],
                ['name' => 'Ondo', 'code' => 'ON'],
                ['name' => 'Osun', 'code' => 'OS'],
                ['name' => 'Oyo', 'code' => 'OY'],
                ['name' => 'Plateau', 'code' => 'PL'],
                ['name' => 'Rivers', 'code' => 'RI'],
                ['name' => 'Sokoto', 'code' => 'SO'],
                ['name' => 'Taraba', 'code' => 'TA'],
                ['name' => 'Yobe', 'code' => 'YO'],
                ['name' => 'Zamfara', 'code' => 'ZA'],
            ];

            foreach ($nigerianStates as $state) {
                State::create(array_merge($state, ['country_id' => $nigeria->id]));
            }
        }

        // US states (top 10)
        if ($us) {
            $usStates = [
                ['name' => 'California', 'code' => 'CA'],
                ['name' => 'Texas', 'code' => 'TX'],
                ['name' => 'Florida', 'code' => 'FL'],
                ['name' => 'New York', 'code' => 'NY'],
                ['name' => 'Pennsylvania', 'code' => 'PA'],
                ['name' => 'Illinois', 'code' => 'IL'],
                ['name' => 'Ohio', 'code' => 'OH'],
                ['name' => 'Georgia', 'code' => 'GA'],
                ['name' => 'North Carolina', 'code' => 'NC'],
                ['name' => 'Michigan', 'code' => 'MI'],
            ];

            foreach ($usStates as $state) {
                State::create(array_merge($state, ['country_id' => $us->id]));
            }
        }

        // UK regions
        if ($gb) {
            $gbStates = [
                ['name' => 'England', 'code' => 'ENG'],
                ['name' => 'Scotland', 'code' => 'SCT'],
                ['name' => 'Wales', 'code' => 'WLS'],
                ['name' => 'Northern Ireland', 'code' => 'NIR'],
            ];

            foreach ($gbStates as $state) {
                State::create(array_merge($state, ['country_id' => $gb->id]));
            }
        }

        // Canadian provinces
        if ($ca) {
            $caStates = [
                ['name' => 'Ontario', 'code' => 'ON'],
                ['name' => 'Quebec', 'code' => 'QC'],
                ['name' => 'British Columbia', 'code' => 'BC'],
                ['name' => 'Alberta', 'code' => 'AB'],
                ['name' => 'Manitoba', 'code' => 'MB'],
                ['name' => 'Saskatchewan', 'code' => 'SK'],
            ];

            foreach ($caStates as $state) {
                State::create(array_merge($state, ['country_id' => $ca->id]));
            }
        }

        // Ghana regions
        if ($gh) {
            $ghStates = [
                ['name' => 'Greater Accra', 'code' => 'AA'],
                ['name' => 'Ashanti', 'code' => 'AH'],
                ['name' => 'Western', 'code' => 'WP'],
                ['name' => 'Eastern', 'code' => 'EP'],
                ['name' => 'Central', 'code' => 'CP'],
                ['name' => 'Northern', 'code' => 'NP'],
            ];

            foreach ($ghStates as $state) {
                State::create(array_merge($state, ['country_id' => $gh->id]));
            }
        }
    }
}