<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\BaseController;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends BaseController
{
    /**
     * Get public services list with filters
     * GET /api/services
     */
    public function index(Request $request)
    {
        try {
            $query = Service::query()
                ->where('status', 'approved')
                ->where('status', '!=', 'inactive')
                ->with(['category', 'subcategory', 'vendor.user']);

            // Search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereJsonContains('tags', $search);
                });
            }

            // Category filter
            if ($request->has('category_id') && !empty($request->category_id)) {
                $query->where('category_id', $request->category_id);
            }

            // Subcategory filter
            if ($request->has('subcategory_id') && !empty($request->subcategory_id)) {
                $query->where('subcategory_id', $request->subcategory_id);
            }

            // Remote/Onsite filters
            if ($request->has('is_remote')) {
                $query->where('is_remote', filter_var($request->is_remote, FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->has('is_onsite')) {
                $query->where('is_onsite', filter_var($request->is_onsite, FILTER_VALIDATE_BOOLEAN));
            }

            // Location filters
            if ($request->has('country_id') && !empty($request->country_id)) {
                $query->where('country_id', $request->country_id);
            }

            if ($request->has('state_id') && !empty($request->state_id)) {
                $query->where('state_id', $request->state_id);
            }

            if ($request->has('city_id') && !empty($request->city_id)) {
                $query->where('city_id', $request->city_id);
            }

            // Price filters
            if ($request->has('min_price') && is_numeric($request->min_price)) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->has('max_price') && is_numeric($request->max_price)) {
                $query->where('price', '<=', $request->max_price);
            }

            // Rating filter
            if ($request->has('min_rating') && is_numeric($request->min_rating)) {
                $query->where('rating', '>=', $request->min_rating);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $allowedSorts = ['created_at', 'price', 'rating', 'total_orders'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
            }

            // Pagination
            $perPage = min((int) $request->get('per_page', 15), 50); // Max 50 per page
            $services = $query->paginate($perPage);

            return $this->success([
                'services' => $services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'title' => $service->title,
                        'description' => substr($service->description, 0, 200) . (strlen($service->description) > 200 ? '...' : ''),
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
                        'rating' => $service->rating,
                        'total_reviews' => $service->total_reviews,
                        'total_orders' => $service->total_orders,
                        'is_remote' => $service->is_remote,
                        'is_onsite' => $service->is_onsite,
                        'vendor' => [
                            'id' => $service->vendor->id,
                            'business_name' => $service->vendor->business_name,
                            'rating' => $service->vendor->rating,
                            'verified' => $service->vendor->verified
                        ],
                        'image_urls' => $service->image_urls ? array_slice($service->image_urls, 0, 1) : [],
                        'created_at' => $service->created_at->toIso8601String()
                    ];
                }),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                    'last_page' => $services->lastPage()
                ],
                'filters_applied' => [
                    'search' => $request->search,
                    'category_id' => $request->category_id,
                    'subcategory_id' => $request->subcategory_id,
                    'is_remote' => $request->is_remote,
                    'is_onsite' => $request->is_onsite,
                    'min_price' => $request->min_price,
                    'max_price' => $request->max_price,
                    'min_rating' => $request->min_rating
                ]
            ], 'Services retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve public services', [
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve services', 500);
        }
    }

    /**
     * Get single service detail with vendor and availability
     * GET /api/services/{id}
     */
    public function show($id)
    {
        try {
            $service = Service::where('id', $id)
                ->where('status', 'approved')
                ->where('status', '!=', 'inactive')
                ->with([
                    'category',
                    'subcategory',
                    'vendor.user',
                    'availabilities' => function ($query) {
                        $query->where('is_active', true)->ordered();
                    }
                ])
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
                        'name' => $service->category->name,
                        'icon_url' => $service->category->icon_url
                    ] : null,
                    'subcategory' => $service->subcategory ? [
                        'id' => $service->subcategory->id,
                        'name' => $service->subcategory->name
                    ] : null,
                    'price' => $service->price,
                    'price_type' => $service->price_type,
                    'duration_minutes' => $service->duration_minutes,
                    'rating' => $service->rating,
                    'total_reviews' => $service->total_reviews,
                    'total_orders' => $service->total_orders,
                    'is_remote' => $service->is_remote,
                    'is_onsite' => $service->is_onsite,
                    'address' => $service->address,
                    'tags' => $service->tags,
                    'image_urls' => $service->image_urls,
                    'vendor' => [
                        'id' => $service->vendor->id,
                        'business_name' => $service->vendor->business_name,
                        'business_type' => $service->vendor->business_type,
                        'rating' => $service->vendor->rating,
                        'total_reviews' => $service->vendor->total_reviews,
                        'verified' => $service->vendor->verified,
                        'owner' => [
                            'name' => $service->vendor->user->full_name ?? 'N/A',
                            'email' => $service->vendor->user->email ?? null
                        ]
                    ],
                    'availability' => $service->availabilities->map(function ($avail) {
                        return [
                            'day_of_week' => $avail->day_of_week,
                            'day_name' => $avail->day_name,
                            'day_short' => $avail->day_short_name,
                            'start_time' => $avail->start_time->format('H:i'),
                            'end_time' => $avail->end_time->format('H:i'),
                            'time_range' => $avail->time_range
                        ];
                    }),
                    'created_at' => $service->created_at->toIso8601String(),
                    'updated_at' => $service->updated_at->toIso8601String()
                ]
            ], 'Service details retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve service detail', [
                'service_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve service', 500);
        }
    }
}