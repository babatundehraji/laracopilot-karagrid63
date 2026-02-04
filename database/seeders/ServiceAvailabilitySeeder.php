<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceAvailability;
use Illuminate\Database\Seeder;

class ServiceAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = Service::all();

        if ($services->isEmpty()) {
            $this->command->warn('No services found. Please run ServiceSeeder first.');
            return;
        }

        $this->command->info('Seeding service availabilities...');

        foreach ($services as $service) {
            // Determine availability pattern
            $pattern = $this->getRandomPattern();

            switch ($pattern) {
                case 'weekdays_only':
                    // Monday to Friday, 9 AM - 5 PM
                    for ($day = 1; $day <= 5; $day++) {
                        ServiceAvailability::create([
                            'service_id' => $service->id,
                            'day_of_week' => $day,
                            'start_time' => '09:00:00',
                            'end_time' => '17:00:00',
                            'is_active' => true
                        ]);
                    }
                    break;

                case 'weekdays_extended':
                    // Monday to Friday, 8 AM - 6 PM
                    for ($day = 1; $day <= 5; $day++) {
                        ServiceAvailability::create([
                            'service_id' => $service->id,
                            'day_of_week' => $day,
                            'start_time' => '08:00:00',
                            'end_time' => '18:00:00',
                            'is_active' => true
                        ]);
                    }
                    break;

                case 'full_week':
                    // Monday to Saturday, 10 AM - 7 PM
                    for ($day = 1; $day <= 6; $day++) {
                        ServiceAvailability::create([
                            'service_id' => $service->id,
                            'day_of_week' => $day,
                            'start_time' => '10:00:00',
                            'end_time' => '19:00:00',
                            'is_active' => true
                        ]);
                    }
                    break;

                case '24_7':
                    // All days, 24 hours
                    for ($day = 0; $day <= 6; $day++) {
                        ServiceAvailability::create([
                            'service_id' => $service->id,
                            'day_of_week' => $day,
                            'start_time' => '00:00:00',
                            'end_time' => '23:59:00',
                            'is_active' => true
                        ]);
                    }
                    break;

                case 'weekend_only':
                    // Saturday and Sunday, 10 AM - 6 PM
                    ServiceAvailability::create([
                        'service_id' => $service->id,
                        'day_of_week' => 0, // Sunday
                        'start_time' => '10:00:00',
                        'end_time' => '18:00:00',
                        'is_active' => true
                    ]);
                    ServiceAvailability::create([
                        'service_id' => $service->id,
                        'day_of_week' => 6, // Saturday
                        'start_time' => '10:00:00',
                        'end_time' => '18:00:00',
                        'is_active' => true
                    ]);
                    break;

                case 'flexible':
                    // Mixed schedule: Weekdays 8-5, Saturday 10-3
                    for ($day = 1; $day <= 5; $day++) {
                        ServiceAvailability::create([
                            'service_id' => $service->id,
                            'day_of_week' => $day,
                            'start_time' => '08:00:00',
                            'end_time' => '17:00:00',
                            'is_active' => true
                        ]);
                    }
                    ServiceAvailability::create([
                        'service_id' => $service->id,
                        'day_of_week' => 6, // Saturday
                        'start_time' => '10:00:00',
                        'end_time' => '15:00:00',
                        'is_active' => true
                    ]);
                    break;

                case 'evening_weekends':
                    // Weekdays 5 PM - 9 PM, Weekends 9 AM - 9 PM
                    for ($day = 1; $day <= 5; $day++) {
                        ServiceAvailability::create([
                            'service_id' => $service->id,
                            'day_of_week' => $day,
                            'start_time' => '17:00:00',
                            'end_time' => '21:00:00',
                            'is_active' => true
                        ]);
                    }
                    ServiceAvailability::create([
                        'service_id' => $service->id,
                        'day_of_week' => 0, // Sunday
                        'start_time' => '09:00:00',
                        'end_time' => '21:00:00',
                        'is_active' => true
                    ]);
                    ServiceAvailability::create([
                        'service_id' => $service->id,
                        'day_of_week' => 6, // Saturday
                        'start_time' => '09:00:00',
                        'end_time' => '21:00:00',
                        'is_active' => true
                    ]);
                    break;
            }
        }

        $this->command->info('Service availabilities seeded successfully.');
    }

    /**
     * Get random availability pattern
     */
    private function getRandomPattern(): string
    {
        $patterns = [
            'weekdays_only',      // 30% - Standard business hours
            'weekdays_extended',  // 25% - Extended business hours
            'full_week',          // 20% - 6 days a week
            '24_7',               // 5%  - Always available (emergency services)
            'weekend_only',       // 5%  - Weekend services
            'flexible',           // 10% - Flexible schedule
            'evening_weekends'    // 5%  - After-hours and weekends
        ];

        $weights = [30, 25, 20, 5, 5, 10, 5];
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);

        $cumulativeWeight = 0;
        foreach ($patterns as $index => $pattern) {
            $cumulativeWeight += $weights[$index];
            if ($random <= $cumulativeWeight) {
                return $pattern;
            }
        }

        return 'weekdays_only'; // Fallback
    }
}