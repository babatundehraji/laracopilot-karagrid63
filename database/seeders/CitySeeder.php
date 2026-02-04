<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\State;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $nigeria = Country::where('iso2', 'NG')->first();
        
        // Lagos State cities
        $lagos = State::where('code', 'LA')->where('country_id', $nigeria?->id)->first();
        if ($lagos) {
            $cities = [
                'Agege', 'Alimosho', 'Amuwo-Odofin', 'Apapa', 'Badagry',
                'Epe', 'Eti-Osa', 'Ibeju-Lekki', 'Ifako-Ijaiye', 'Ikeja',
                'Ikorodu', 'Kosofe', 'Lagos Island', 'Lagos Mainland', 'Lekki',
                'Mushin', 'Ojo', 'Oshodi-Isolo', 'Shomolu', 'Surulere',
                'Victoria Island', 'Yaba'
            ];
            foreach ($cities as $city) {
                City::create(['name' => $city, 'state_id' => $lagos->id]);
            }
        }

        // FCT (Abuja) cities
        $fct = State::where('code', 'FC')->where('country_id', $nigeria?->id)->first();
        if ($fct) {
            $cities = [
                'Abuja Municipal', 'Asokoro', 'Central Business District', 'Garki',
                'Gwarinpa', 'Jabi', 'Karu', 'Kubwa', 'Lugbe', 'Maitama',
                'Nyanya', 'Utako', 'Wuse', 'Wuse II'
            ];
            foreach ($cities as $city) {
                City::create(['name' => $city, 'state_id' => $fct->id]);
            }
        }

        // Rivers State cities
        $rivers = State::where('code', 'RI')->where('country_id', $nigeria?->id)->first();
        if ($rivers) {
            $cities = [
                'Port Harcourt', 'Obio-Akpor', 'Eleme', 'Ikwerre', 'Oyigbo',
                'Okrika', 'Bonny', 'Degema'
            ];
            foreach ($cities as $city) {
                City::create(['name' => $city, 'state_id' => $rivers->id]);
            }
        }

        // Kano State cities
        $kano = State::where('code', 'KN')->where('country_id', $nigeria?->id)->first();
        if ($kano) {
            $cities = [
                'Kano Municipal', 'Fagge', 'Dala', 'Gwale', 'Tarauni',
                'Nassarawa', 'Kumbotso', 'Ungogo'
            ];
            foreach ($cities as $city) {
                City::create(['name' => $city, 'state_id' => $kano->id]);
            }
        }

        // Oyo State cities
        $oyo = State::where('code', 'OY')->where('country_id', $nigeria?->id)->first();
        if ($oyo) {
            $cities = [
                'Ibadan North', 'Ibadan South', 'Ibadan North-East', 'Ibadan North-West',
                'Ibadan South-East', 'Ibadan South-West', 'Ogbomosho North',
                'Ogbomosho South', 'Oyo East', 'Oyo West'
            ];
            foreach ($cities as $city) {
                City::create(['name' => $city, 'state_id' => $oyo->id]);
            }
        }

        // US - California cities
        $california = State::where('code', 'CA')->whereHas('country', function($q) {
            $q->where('iso2', 'US');
        })->first();
        if ($california) {
            $cities = [
                'Los Angeles', 'San Diego', 'San Jose', 'San Francisco',
                'Fresno', 'Sacramento', 'Long Beach', 'Oakland',
                'Bakersfield', 'Anaheim', 'Santa Ana', 'Riverside'
            ];
            foreach ($cities as $city) {
                City::create(['name' => $city, 'state_id' => $california->id]);
            }
        }

        // US - Texas cities
        $texas = State::where('code', 'TX')->whereHas('country', function($q) {
            $q->where('iso2', 'US');
        })->first();
        if ($texas) {
            $cities = [
                'Houston', 'San Antonio', 'Dallas', 'Austin', 'Fort Worth',
                'El Paso', 'Arlington', 'Corpus Christi', 'Plano'
            ];
            foreach ($cities as $city) {
                City::create(['name' => $city, 'state_id' => $texas->id]);
            }
        }

        // US - Florida cities
        $florida = State::where('code', 'FL')->whereHas('country', function($q) {
            $q->where('iso2', 'US');
        })->first();
        if ($florida) {
            $cities = [
                'Jacksonville', 'Miami', 'Tampa', 'Orlando', 'St. Petersburg',
                'Hialeah', 'Tallahassee', 'Fort Lauderdale', 'Port St. Lucie'
            ];
            foreach ($cities as $city) {
                City::create(['name' => $city, 'state_id' => $florida->id]);
            }
        }

        // UK - England cities
        $england = State::where('code', 'ENG')->whereHas('country', function($q) {
            $q->where('iso2', 'GB');
        })->first();
        if ($england) {
            $cities = [
                'London', 'Birmingham', 'Manchester', 'Liverpool', 'Leeds',
                'Sheffield', 'Bristol', 'Newcastle', 'Nottingham', 'Southampton'
            ];
            foreach ($cities as $city) {
                City::create(['name' => $city, 'state_id' => $england->id]);
            }
        }
    }
}