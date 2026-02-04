<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreServiceReviewRequest;
use App\Http\Requests\Api\UpdateServiceReviewRequest;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceReview;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceReviewController extends BaseController
{
    /**
     * Get all reviews for a service (public)
     * GET /api/services/{service}/reviews
     */
    public function index(Request $request, $serviceId)
    {
        try {
            $service = Service::find($serviceId);

            if (!$service) {
                return $this->error('Service not found', 404);
            }

            // Get visible reviews only
            $reviews = ServiceReview::where('service_id', $serviceId)
                ->visible()
                ->with('user:id,first_name,last_name')
                ->recent()
                ->paginate(20);

            return $this->success([
                'service' => [
                    'id' => $service->id,
                    'title' => $service->title,
                    'rating' => $service->rating,
                    'total_reviews' => $service->total_reviews
                ],
                'reviews' => $reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'reviewer' => [
                            'name' => $review->reviewer_name
                        ],
                        'created_at' => $review->created_at->toIso8601String()
                    ];
                }),
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                    'last_page' => $reviews->lastPage()
                ]
            ], 'Reviews retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve service reviews', [
                'service_id' => $serviceId,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve reviews', 500);
        }
    }

    /**
     * Create a new review for a service
     * POST /api/services/{service}/reviews
     */
    public function store(StoreServiceReviewRequest $request, $serviceId)
    {
        try {
            $user = $request->user();

            // Check if service exists
            $service = Service::find($serviceId);

            if (!$service) {
                return $this->error('Service not found', 404);
            }

            // Check if order exists and belongs to user
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->where('service_id', $serviceId)
                ->first();

            if (!$order) {
                return $this->error('Order not found or does not belong to you', 404);
            }

            // Check if order is completed
            if ($order->status !== 'completed') {
                return $this->error('You can only review completed orders', 403);
            }

            // Check if review already exists for this order
            $existingReview = ServiceReview::where('order_id', $order->id)->first();

            if ($existingReview) {
                return $this->error('You have already reviewed this order', 409);
            }

            DB::beginTransaction();

            // Create review
            $review = ServiceReview::create([
                'service_id' => $service->id,
                'vendor_id' => $service->vendor_id,
                'user_id' => $user->id,
                'order_id' => $order->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => 'visible'
            ]);

            // Recalculate ratings
            ServiceReview::recalculateServiceRating($service->id);
            ServiceReview::recalculateVendorRating($service->vendor_id);

            DB::commit();

            // Log activity
            ActivityLogger::log(
                $user->id,
                'review_created',
                "User {$user->full_name} reviewed service '{$service->title}' with {$request->rating} stars",
                'App\\Models\\ServiceReview',
                $review->id
            );

            Log::info('Service review created', [
                'user_id' => $user->id,
                'service_id' => $service->id,
                'order_id' => $order->id,
                'rating' => $request->rating
            ]);

            return $this->success([
                'review' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at->toIso8601String()
                ],
                'service_rating' => [
                    'average' => $service->fresh()->rating,
                    'total_reviews' => $service->fresh()->total_reviews
                ]
            ], 'Review submitted successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create service review', [
                'user_id' => $request->user()->id,
                'service_id' => $serviceId,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to submit review', 500);
        }
    }

    /**
     * Update existing review
     * PUT /api/services/{service}/reviews/{id}
     */
    public function update(UpdateServiceReviewRequest $request, $serviceId, $reviewId)
    {
        try {
            $user = $request->user();

            // Check if service exists
            $service = Service::find($serviceId);

            if (!$service) {
                return $this->error('Service not found', 404);
            }

            // Find review
            $review = ServiceReview::where('id', $reviewId)
                ->where('service_id', $serviceId)
                ->first();

            if (!$review) {
                return $this->error('Review not found', 404);
            }

            // Check ownership
            if ($review->user_id !== $user->id) {
                return $this->error('You can only edit your own reviews', 403);
            }

            DB::beginTransaction();

            // Update review
            $updateData = array_filter([
                'rating' => $request->rating,
                'comment' => $request->comment
            ], function ($value) {
                return !is_null($value);
            });

            if (!empty($updateData)) {
                $review->update($updateData);

                // Recalculate ratings only if rating changed
                if (isset($updateData['rating'])) {
                    ServiceReview::recalculateServiceRating($service->id);
                    ServiceReview::recalculateVendorRating($service->vendor_id);
                }
            }

            DB::commit();

            // Log activity
            ActivityLogger::log(
                $user->id,
                'review_updated',
                "User {$user->full_name} updated review for service '{$service->title}'",
                'App\\Models\\ServiceReview',
                $review->id
            );

            Log::info('Service review updated', [
                'user_id' => $user->id,
                'review_id' => $review->id,
                'service_id' => $service->id,
                'fields_updated' => array_keys($updateData)
            ]);

            return $this->success([
                'review' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'updated_at' => $review->updated_at->toIso8601String()
                ],
                'service_rating' => [
                    'average' => $service->fresh()->rating,
                    'total_reviews' => $service->fresh()->total_reviews
                ]
            ], 'Review updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update service review', [
                'user_id' => $request->user()->id,
                'review_id' => $reviewId,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to update review', 500);
        }
    }

    /**
     * Delete review
     * DELETE /api/services/{service}/reviews/{id}
     */
    public function destroy(Request $request, $serviceId, $reviewId)
    {
        try {
            $user = $request->user();

            // Check if service exists
            $service = Service::find($serviceId);

            if (!$service) {
                return $this->error('Service not found', 404);
            }

            // Find review
            $review = ServiceReview::where('id', $reviewId)
                ->where('service_id', $serviceId)
                ->first();

            if (!$review) {
                return $this->error('Review not found', 404);
            }

            // Check ownership
            if ($review->user_id !== $user->id) {
                return $this->error('You can only delete your own reviews', 403);
            }

            DB::beginTransaction();

            $vendorId = $review->vendor_id;
            $review->delete();

            // Recalculate ratings
            ServiceReview::recalculateServiceRating($service->id);
            ServiceReview::recalculateVendorRating($vendorId);

            DB::commit();

            // Log activity
            ActivityLogger::log(
                $user->id,
                'review_deleted',
                "User {$user->full_name} deleted review for service '{$service->title}'",
                'App\\Models\\ServiceReview',
                $reviewId
            );

            Log::info('Service review deleted', [
                'user_id' => $user->id,
                'review_id' => $reviewId,
                'service_id' => $service->id
            ]);

            return $this->success([
                'service_rating' => [
                    'average' => $service->fresh()->rating,
                    'total_reviews' => $service->fresh()->total_reviews
                ]
            ], 'Review deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete service review', [
                'user_id' => $request->user()->id,
                'review_id' => $reviewId,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to delete review', 500);
        }
    }
}