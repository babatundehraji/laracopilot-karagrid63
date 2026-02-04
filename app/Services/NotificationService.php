<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create notification for user and optionally send email
     *
     * @param int $userId User ID to notify
     * @param string $title Notification title
     * @param string $body Notification body/message
     * @param string|null $type Notification type (e.g., 'order', 'message', 'dispute')
     * @param mixed $data Additional data (will be stored as JSON)
     * @param bool $sendEmail Whether to send email notification
     * @return Notification|null Created notification or null on failure
     */
    public static function notifyUser(
        int $userId,
        string $title,
        string $body,
        ?string $type = null,
        $data = null,
        bool $sendEmail = false
    ): ?Notification {
        try {
            // Create notification
            $notification = Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'data' => $data,
                'is_read' => false,
                'read_at' => null
            ]);

            // Send email if requested
            if ($sendEmail) {
                $user = User::find($userId);
                
                if ($user && $user->email) {
                    // Build simple HTML email message
                    $htmlMessage = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <h2 style='color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;'>{$title}</h2>
                            <p style='color: #555; line-height: 1.6; font-size: 16px;'>{$body}</p>
                            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                            <p style='color: #999; font-size: 12px;'>This is an automated notification from our system.</p>
                        </div>
                    ";

                    // Send email using EmailService
                    EmailService::send(
                        $user->email,
                        $title,
                        $htmlMessage,
                        true // isHtml
                    );

                    Log::info('Notification email sent', [
                        'user_id' => $userId,
                        'notification_id' => $notification->id,
                        'title' => $title
                    ]);
                }
            }

            Log::info('Notification created', [
                'user_id' => $userId,
                'notification_id' => $notification->id,
                'type' => $type,
                'send_email' => $sendEmail
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to create notification', [
                'user_id' => $userId,
                'title' => $title,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }
}