<?php

namespace Database\Seeders;

use App\Models\ServiceAvailability;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ServiceAvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::where('status', 'approved')->get();

        foreach ($services as $service) {
            // Create availability for next 30 days
            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::now()->addDays($i);
                
                // 80% chance the service is available
                $isAvailable = fake()->boolean(80);
                
                // For onsite services with time slots
                if ($service->is_onsite && fake()->boolean(70)) {
                    // Create morning slot
                    ServiceAvailability::create([
                        'service_id' => $service->id,
                        'date' => $date->toDateString(),
                        'start_time' => '09:00:00',
                        'end_time' => '12:00:00',
                        'is_available' => $isAvailable
                    ]);
                } else {
                    // Full day availability
                    ServiceAvailability::create([
                        'service_id' => $service->id,
                        'date' => $date->toDateString(),
                        'start_time' => null,
                        'end_time' => null,
                        'is_available' => $isAvailable
                    ]);
                }
            }
        }

        // Create some specific time slot availabilities for a few services
        $timeSlotServices = Service::approved()->inRandomOrder()->limit(5)->get();
        
        foreach ($timeSlotServices as $service) {
            for ($i = 0; $i < 14; $i++) {
                $date = Carbon::now()->addDays($i);
                
                // Skip if already exists
                if (ServiceAvailability::where('service_id', $service->id)
                    ->where('date', $date->toDateString())
                    ->exists()) {
                    continue;
                }

                // Create multiple time slots for the same day
                $timeSlots = [
                    ['start' => '08:00:00', 'end' => '11:00:00'],
                    ['start' => '11:00:00', 'end' => '14:00:00'],
                    ['start' => '14:00:00', 'end' => '17:00:00'],
                    ['start' => '17:00:00', 'end' => '20:00:00'],
                ];

                foreach ($timeSlots as $slot) {
                    ServiceAvailability::create([
                        'service_id' => $service->id,
                        'date' => $date->toDateString(),
                        'start_time' => $slot['start'],
                        'end_time' => $slot['end'],
                        'is_available' => fake()->boolean(70)
                    ]);
                }
            }
        }
    }
}