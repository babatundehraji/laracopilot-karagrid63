<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\StoreServiceRequest;
use App\Http\Requests\Api\UpdateServiceRequest;
use App\Http\Requests\Api\UpdateServiceStatusRequest;
use App\Models\Service;
use App\Models\ServiceAvailability;
use App\Models\Vendor;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceController extends BaseController
{
    /**
     * Get all services for authenticated vendor
     * GET /api/vendor/services
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // Find vendor record
            $vendor = Vendor::where('user_id', $user->id)->first();

            if (!$vendor) {
                return $this->error('Vendor profile not found', 404);
            }

            // Get vendor's services
            $services = Service::where('vendor_id', $vendor->id)
                ->with(['category', 'subcategory'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return $this->success([
                'services' => $services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'title' => $service->title,
                        'description' => $service->description,
                        'category' => $service->category ? [
                            'id' => $service->category->id,
                            'name' => $service->category->name
                        ] : null,
                        'subcategory' => $service->subcategory ? [
                            'id' => $service->subcategory->id,
                            'name' => $service->subcategory->name
                        ] : null,
                        'price' => $service->price,
                        'price_type' => $service->price_type,
                        'status' => $service->status,
                        'rating' => $service->rating,
                        'total_reviews' => $service->total_reviews,
                        'total_orders' => $service->total_orders,
                        'is_remote' => $service->is_remote,
                        'is_onsite' => $service->is_onsite,
                        'created_at' => $service->created_at->toIso8601String(),
                        'updated_at' => $service->updated_at->toIso8601String()
                    ];
                }),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                    'last_page' => $services->lastPage()
                ]
            ], 'Services retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve vendor services', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve services', 500);
        }
    }

    /**
     * Get single service detail
     * GET /api/vendor/services/{id}
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Find vendor record
            $vendor = Vendor::where('user_id', $user->id)->first();

            if (!$vendor) {
                return $this->error('Vendor profile not found', 404);
            }

            // Get service (must belong to vendor)
            $service = Service::where('id', $id)
                ->where('vendor_id', $vendor->id)
                ->with(['category', 'subcategory', 'availabilities' => function ($query) {
                    $query->ordered();
                }])
                ->first();

            if (!$service) {
                return $this->error('Service not found', 404);
            }

            return $this->success([
                'service' => [
                    'id' => $service->id,
                    'title' => $service->title,
                    'description' => $service->description,
                    'category' => $service->category ? [
                        'id' => $service->category->id,
                        'name' => $service->category->name
                    ] : null,
                    'subcategory' => $service->subcategory ? [
                        'id' => $service->subcategory->id,
                        'name' => $service->subcategory->name
                    ] : null,
                    'price' => $service->price,
                    'price_type' => $service->price_type,
                    'duration_minutes' => $service->duration_minutes,
                    'status' => $service->status,
                    'rating' => $service->rating,
                    'total_reviews' => $service->total_reviews,
                    'total_orders' => $service->total_orders,
                    'is_remote' => $service->is_remote,
                    'is_onsite' => $service->is_onsite,
                    'country_id' => $service->country_id,
                    'state_id' => $service->state_id,
                    'city_id' => $service->city_id,
                    'address' => $service->address,
                    'tags' => $service->tags,
                    'image_urls' => $service->image_urls,
                    'availability' => $service->availabilities->map(function ($avail) {
                        return [
                            'id' => $avail->id,
                            'day_of_week' => $avail->day_of_week,
                            'day_name' => $avail->day_name,
                            'start_time' => $avail->start_time->format('H:i'),
                            'end_time' => $avail->end_time->format('H:i'),
                            'is_active' => $avail->is_active
                        ];
                    }),
                    'created_at' => $service->created_at->toIso8601String(),
                    'updated_at' => $service->updated_at->toIso8601String()
                ]
            ], 'Service retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve service detail', [
                'user_id' => $request->user()->id,
                'service_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve service', 500);
        }
    }

    /**
     * Create new service with availability
     * POST /api/vendor/services
     */
    public function store(StoreServiceRequest $request)
    {
        try {
            $user = $request->user();

            // Find vendor record
            $vendor = Vendor::where('user_id', $user->id)->first();

            if (!$vendor) {
                return $this->error('Vendor profile not found. Please complete vendor application first.', 404);
            }

            // Check if vendor is approved
            if ($vendor->status !== 'approved') {
                return $this->error('Your vendor account is not approved yet. Status: ' . $vendor->status, 403);
            }

            // Check for duplicate service title for this vendor
            $existingService = Service::where('vendor_id', $vendor->id)
                ->where('title', $request->title)
                ->first();

            if ($existingService) {
                return $this->error('You already have a service with this title', 409);
            }

            DB::beginTransaction();

            // Create service
            $service = Service::create([
                'vendor_id' => $vendor->id,
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'price_type' => $request->price_type,
                'duration_minutes' => $request->duration_minutes,
                'is_remote' => $request->is_remote,
                'is_onsite' => $request->is_onsite,
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                'city_id' => $request->city_id,
                'address' => $request->address,
                'tags' => $request->tags,
                'image_urls' => $request->image_urls,
                'status' => 'pending', // Default status
                'rating' => 0,
                'total_reviews' => 0,
                'total_orders' => 0
            ]);

            // Create availability if provided
            if ($request->has('availability') && is_array($request->availability)) {
                foreach ($request->availability as $avail) {
                    ServiceAvailability::updateOrCreate(
                        [
                            'service_id' => $service->id,
                            'day_of_week' => $avail['day_of_week']
                        ],
                        [
                            'start_time' => $avail['start_time'],
                            'end_time' => $avail['end_time'],
                            'is_active' => $avail['is_active'] ?? true
                        ]
                    );
                }
            }

            DB::commit();

            // Log activity
            ActivityLogger::log(
                $user->id,
                'service_created',
                "Service '{$service->title}' created by vendor {$vendor->business_name}",
                'App\\Models\\Service',
                $service->id
            );

            Log::info('Service created', [
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'service_id' => $service->id,
                'title' => $service->title
            ]);

            return $this->success([
                'service' => [
                    'id' => $service->id,
                    'title' => $service->title,
                    'status' => $service->status,
                    'price' => $service->price,
                    'price_type' => $service->price_type,
                    'created_at' => $service->created_at->toIso8601String()
                ]
            ], 'Service created successfully. Pending admin approval.', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create service', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to create service', 500);
        }
    }

    /**
     * Update existing service
     * PUT /api/vendor/services/{id}
     */
    public function update(UpdateServiceRequest $request, $id)
    {
        try {
            $user = $request->user();

            // Find vendor record
            $vendor = Vendor::where('user_id', $user->id)->first();

            if (!$vendor) {
                return $this->error('Vendor profile not found', 404);
            }

            // Get service (must belong to vendor)
            $service = Service::where('id', $id)
                ->where('vendor_id', $vendor->id)
                ->first();

            if (!$service) {
                return $this->error('Service not found', 404);
            }

            // Check for duplicate title if title is being changed
            if ($request->has('title') && $request->title !== $service->title) {
                $existingService = Service::where('vendor_id', $vendor->id)
                    ->where('title', $request->title)
                    ->where('id', '!=', $service->id)
                    ->first();

                if ($existingService) {
                    return $this->error('You already have another service with this title', 409);
                }
            }

            DB::beginTransaction();

            // Update service (only allowed fields)
            $updateData = array_filter([
                'title' => $request->title,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'price' => $request->price,
                'price_type' => $request->price_type,
                'duration_minutes' => $request->duration_minutes,
                'is_remote' => $request->is_remote,
                'is_onsite' => $request->is_onsite,
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                'city_id' => $request->city_id,
                'address' => $request->address,
                'tags' => $request->tags,
                'image_urls' => $request->image_urls
            ], function ($value) {
                return !is_null($value);
            });

            if (!empty($updateData)) {
                $service->update($updateData);
            }

            // Update availability if provided
            if ($request->has('availability') && is_array($request->availability)) {
                foreach ($request->availability as $avail) {
                    ServiceAvailability::updateOrCreate(
                        [
                            'service_id' => $service->id,
                            'day_of_week' => $avail['day_of_week']
                        ],
                        [
                            'start_time' => $avail['start_time'],
                            'end_time' => $avail['end_time'],
                            'is_active' => $avail['is_active'] ?? true
                        ]
                    );
                }
            }

            DB::commit();

            // Log activity
            ActivityLogger::log(
                $user->id,
                'service_updated',
                "Service '{$service->title}' updated by vendor {$vendor->business_name}",
                'App\\Models\\Service',
                $service->id
            );

            Log::info('Service updated', [
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'service_id' => $service->id,
                'fields_updated' => array_keys($updateData)
            ]);

            return $this->success([
                'service' => [
                    'id' => $service->id,
                    'title' => $service->title,
                    'status' => $service->status,
                    'updated_at' => $service->updated_at->toIso8601String()
                ]
            ], 'Service updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update service', [
                'user_id' => $request->user()->id,
                'service_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to update service', 500);
        }
    }

    /**
     * Toggle service status (active/inactive only)
     * PATCH /api/vendor/services/{id}/status
     */
    public function updateStatus(UpdateServiceStatusRequest $request, $id)
    {
        try {
            $user = $request->user();

            // Find vendor record
            $vendor = Vendor::where('user_id', $user->id)->first();

            if (!$vendor) {
                return $this->error('Vendor profile not found', 404);
            }

            // Get service (must belong to vendor)
            $service = Service::where('id', $id)
                ->where('vendor_id', $vendor->id)
                ->first();

            if (!$service) {
                return $this->error('Service not found', 404);
            }

            // Vendor can only toggle between active and inactive
            if (!in_array($request->status, ['active', 'inactive'])) {
                return $this->error('You can only set status to active or inactive', 400);
            }

            // If service is pending or rejected, vendor cannot activate it
            if (in_array($service->status, ['pending', 'rejected']) && $request->status === 'active') {
                return $this->error('Cannot activate service with status: ' . $service->status, 403);
            }

            $oldStatus = $service->status;
            $service->update(['status' => $request->status]);

            // Log activity
            ActivityLogger::log(
                $user->id,
                'service_status_changed',
                "Service '{$service->title}' status changed from {$oldStatus} to {$request->status}",
                'App\\Models\\Service',
                $service->id
            );

            Log::info('Service status updated', [
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'service_id' => $service->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ]);

            return $this->success([
                'service' => [
                    'id' => $service->id,
                    'title' => $service->title,
                    'status' => $service->status,
                    'updated_at' => $service->updated_at->toIso8601String()
                ]
            ], 'Service status updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update service status', [
                'user_id' => $request->user()->id,
                'service_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to update service status', 500);
        }
    }

    /**
     * Delete service
     * DELETE /api/vendor/services/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Find vendor record
            $vendor = Vendor::where('user_id', $user->id)->first();

            if (!$vendor) {
                return $this->error('Vendor profile not found', 404);
            }

            // Get service (must belong to vendor)
            $service = Service::where('id', $id)
                ->where('vendor_id', $vendor->id)
                ->first();

            if (!$service) {
                return $this->error('Service not found', 404);
            }

            // Check if service has active orders
            $hasActiveOrders = $service->orders()
                ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                ->exists();

            if ($hasActiveOrders) {
                return $this->error('Cannot delete service with active orders', 409);
            }

            $serviceTitle = $service->title;

            // Delete service (availabilities will cascade delete)
            $service->delete();

            // Log activity
            ActivityLogger::log(
                $user->id,
                'service_deleted',
                "Service '{$serviceTitle}' deleted by vendor {$vendor->business_name}",
                'App\\Models\\Service',
                $id
            );

            Log::info('Service deleted', [
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'service_id' => $id,
                'title' => $serviceTitle
            ]);

            return $this->success(null, 'Service deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete service', [
                'user_id' => $request->user()->id,
                'service_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to delete service', 500);
        }
    }
}