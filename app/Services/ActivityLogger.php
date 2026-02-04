<?php

namespace App\Services;

use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    /**
     * Log an activity (queued for background processing)
     *
     * @param int|null $userId User ID performing the action (null for guest actions)
     * @param string $action Action performed (e.g., 'user.login', 'order.created')
     * @param string|null $description Human-readable description
     * @param string|null $subjectType Model class name (e.g., 'App\\Models\\Order')
     * @param int|null $subjectId Subject model ID
     * @return void
     */
    public static function log(
        ?int $userId,
        string $action,
        ?string $description = null,
        ?string $subjectType = null,
        ?int $subjectId = null
    ): void {
        try {
            LogActivityJob::dispatch(
                $userId,
                $action,
                $description,
                $subjectType,
                $subjectId
            );

            Log::debug('Activity log dispatched', [
                'user_id' => $userId,
                'action' => $action
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch activity log', [
                'user_id' => $userId,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log user authentication activity
     */
    public static function logAuth(?int $userId, string $action, string $email): void
    {
        self::log(
            $userId,
            $action,
            "User {$email} performed {$action}"
        );
    }

    /**
     * Log user login
     */
    public static function logLogin(int $userId, string $email): void
    {
        self::logAuth($userId, 'user.login', $email);
    }

    /**
     * Log user logout
     */
    public static function logLogout(int $userId, string $email): void
    {
        self::logAuth($userId, 'user.logout', $email);
    }

    /**
     * Log user registration
     */
    public static function logRegistration(int $userId, string $email): void
    {
        self::logAuth($userId, 'user.registered', $email);
    }

    /**
     * Log password reset
     */
    public static function logPasswordReset(int $userId, string $email): void
    {
        self::logAuth($userId, 'user.password_reset', $email);
    }

    /**
     * Log email verification
     */
    public static function logEmailVerification(int $userId, string $email): void
    {
        self::logAuth($userId, 'user.email_verified', $email);
    }

    /**
     * Log model creation
     */
    public static function logCreated(int $userId, string $modelType, int $modelId, ?string $description = null): void
    {
        self::log(
            $userId,
            'model.created',
            $description ?? "Created {$modelType}",
            $modelType,
            $modelId
        );
    }

    /**
     * Log model update
     */
    public static function logUpdated(int $userId, string $modelType, int $modelId, ?string $description = null): void
    {
        self::log(
            $userId,
            'model.updated',
            $description ?? "Updated {$modelType}",
            $modelType,
            $modelId
        );
    }

    /**
     * Log model deletion
     */
    public static function logDeleted(int $userId, string $modelType, int $modelId, ?string $description = null): void
    {
        self::log(
            $userId,
            'model.deleted',
            $description ?? "Deleted {$modelType}",
            $modelType,
            $modelId
        );
    }
}