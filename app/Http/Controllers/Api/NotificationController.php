<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends BaseController
{
    /**
     * Get all notifications for authenticated user
     * GET /api/notifications
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $notifications = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return $this->success([
                'notifications' => $notifications->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'body' => $notification->body,
                        'type' => $notification->type,
                        'data' => $notification->data,
                        'is_read' => $notification->is_read,
                        'read_at' => $notification->read_at ? $notification->read_at->toIso8601String() : null,
                        'created_at' => $notification->created_at->toIso8601String()
                    ];
                }),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'last_page' => $notifications->lastPage()
                ],
                'unread_count' => Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count()
            ], 'Notifications retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve notifications', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve notifications', 500);
        }
    }

    /**
     * Mark single notification as read
     * POST /api/notifications/{id}/read
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Find notification
            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return $this->error('Notification not found', 404);
            }

            // Update if not already read
            if (!$notification->is_read) {
                $notification->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

                Log::info('Notification marked as read', [
                    'user_id' => $user->id,
                    'notification_id' => $notification->id
                ]);
            }

            return $this->success([
                'notification' => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'is_read' => $notification->is_read,
                    'read_at' => $notification->read_at->toIso8601String(),
                    'created_at' => $notification->created_at->toIso8601String()
                ]
            ], 'Notification marked as read');
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'user_id' => $request->user()->id,
                'notification_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to mark notification as read', 500);
        }
    }

    /**
     * Mark all notifications as read for authenticated user
     * POST /api/notifications/read-all
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user();

            // Update all unread notifications
            $updated = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            Log::info('All notifications marked as read', [
                'user_id' => $user->id,
                'count' => $updated
            ]);

            return $this->success([
                'marked_count' => $updated
            ], "All notifications marked as read");
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to mark all notifications as read', 500);
        }
    }
}