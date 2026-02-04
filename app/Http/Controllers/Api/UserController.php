<?php

namespace App\Http\Controllers\Api;

use App\Models\VerificationCode;
use App\Services\ActivityLogger;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    /**
     * Get authenticated user profile
     * GET /api/user
     */
    public function show(Request $request)
    {
        try {
            $user = $request->user();

            return $this->success([
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone_code' => $user->phone_code,
                    'phone' => $user->phone,
                    'full_phone' => $user->full_phone,
                    'role' => $user->role,
                    'status' => $user->status,
                    'email_verified' => $user->is_email_verified,
                    'phone_verified' => $user->is_phone_verified,
                    'avatar' => $user->avatar,
                    'bio' => $user->bio,
                    'country' => $user->country,
                    'state' => $user->state,
                    'city' => $user->city,
                    'full_location' => $user->full_location,
                    'timezone' => $user->timezone,
                    'last_login' => $user->last_login,
                    'created_at' => $user->created_at->toIso8601String(),
                    'updated_at' => $user->updated_at->toIso8601String()
                ]
            ], 'User profile retrieved');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user profile', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve user profile', 500);
        }
    }

    /**
     * Update user profile
     * PUT /api/user
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'avatar_url' => 'nullable|url|max:500',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, ['errors' => $validator->errors()]);
        }

        try {
            $user = $request->user();

            // Only update provided fields
            $data = array_filter($request->only([
                'first_name',
                'last_name',
                'avatar_url',
                'country',
                'state',
                'city',
                'timezone',
                'bio'
            ]), function($value) {
                return !is_null($value);
            });

            if (empty($data)) {
                return $this->error('No fields provided for update', 400);
            }

            // Update user profile
            $user->update($data);

            // Log activity
            ActivityLogger::log(
                $user->id,
                'user_profile_updated',
                "User {$user->full_name} updated profile",
                'App\\Models\\User',
                $user->id
            );

            Log::info('User profile updated', [
                'user_id' => $user->id,
                'fields_updated' => array_keys($data)
            ]);

            return $this->success([
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'bio' => $user->bio,
                    'country' => $user->country,
                    'state' => $user->state,
                    'city' => $user->city,
                    'full_location' => $user->full_location,
                    'timezone' => $user->timezone,
                    'updated_at' => $user->updated_at->toIso8601String()
                ]
            ], 'Profile updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update user profile', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to update profile', 500);
        }
    }

    /**
     * Request password change code
     * POST /api/user/change-password/request-code
     */
    public function requestPasswordChangeCode(Request $request)
    {
        try {
            $user = $request->user();

            // Generate verification code
            $verification = VerificationCode::generate(
                $user->email,
                'password_change',
                $user->id,
                15 // 15 minutes expiry
            );

            // Build HTML message
            $msg = '
                <h2>Password Change Request</h2>
                <p>Hi ' . htmlspecialchars($user->full_name) . ',</p>
                <p>You requested to change your password. Use the code below to proceed:</p>
                <div class="code-box">
                    <div class="code-label">Password Change Code</div>
                    <div class="code">' . $verification->code . '</div>
                </div>
                <div class="info-box">
                    <p><strong>This code will expire in 15 minutes.</strong></p>
                </div>
                <p>Enter this code in the password change confirmation screen to set your new password.</p>
                <div class="warning-box">
                    <p><strong>Security Notice:</strong> If you did not request a password change, please ignore this email and ensure your account is secure.</p>
                </div>
            ';

            // Send email
            EmailService::send(
                $user->email,
                'Password Change Code',
                $msg,
                true // queue
            );

            // Log activity
            ActivityLogger::log(
                $user->id,
                'user_change_password_code_requested',
                "Password change code requested for {$user->email}",
                'App\\Models\\User',
                $user->id
            );

            Log::info('Password change code requested', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return $this->success([
                'email' => $user->email,
                'expires_in_minutes' => 15
            ], 'Password change code sent to your email');
        } catch (\Exception $e) {
            Log::error('Failed to request password change code', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to send password change code', 500);
        }
    }

    /**
     * Confirm password change with code
     * POST /api/user/change-password/confirm
     */
    public function confirmPasswordChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, ['errors' => $validator->errors()]);
        }

        try {
            $user = $request->user();

            // Validate verification code
            $verification = VerificationCode::where('email', $user->email)
                ->where('user_id', $user->id)
                ->where('code', $request->code)
                ->where('type', 'password_change')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->first();

            if (!$verification) {
                return $this->error('Invalid or expired verification code', 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Mark code as used
            $verification->markAsUsed();

            // Log activity
            ActivityLogger::log(
                $user->id,
                'user_password_changed',
                "Password changed for {$user->email}",
                'App\\Models\\User',
                $user->id
            );

            Log::info('Password changed successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return $this->success(['email' => $user->email], 'Password changed successfully');
        } catch (\Exception $e) {
            Log::error('Failed to change password', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to change password', 500);
        }
    }

    /**
     * Request email change code
     * POST /api/user/change-email/request-code
     */
    public function requestEmailChangeCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_email' => 'required|email|max:191|unique:users,email'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, ['errors' => $validator->errors()]);
        }

        try {
            $user = $request->user();
            $newEmail = $request->new_email;

            // Generate verification code for new email
            $verification = VerificationCode::generate(
                $newEmail,
                'email_change',
                $user->id,
                15 // 15 minutes expiry
            );

            // Build HTML message
            $msg = '
                <h2>Email Change Verification</h2>
                <p>Hi ' . htmlspecialchars($user->full_name) . ',</p>
                <p>You requested to change your email address to this email. Use the code below to confirm:</p>
                <div class="code-box">
                    <div class="code-label">Email Change Verification Code</div>
                    <div class="code">' . $verification->code . '</div>
                </div>
                <div class="info-box">
                    <p><strong>This code will expire in 15 minutes.</strong></p>
                </div>
                <p>Enter this code to confirm the email change.</p>
                <div class="warning-box">
                    <p><strong>Security Notice:</strong> If you did not request this email change, someone may be attempting to access your account. Please secure your account immediately.</p>
                </div>
            ';

            // Send email to new address
            EmailService::send(
                $newEmail,
                'Email Change Verification Code',
                $msg,
                true // queue
            );

            // Log activity
            ActivityLogger::log(
                $user->id,
                'user_change_email_code_requested',
                "Email change code requested from {$user->email} to {$newEmail}",
                'App\\Models\\User',
                $user->id
            );

            Log::info('Email change code requested', [
                'user_id' => $user->id,
                'old_email' => $user->email,
                'new_email' => $newEmail
            ]);

            return $this->success([
                'new_email' => $newEmail,
                'expires_in_minutes' => 15
            ], 'Verification code sent to new email address');
        } catch (\Exception $e) {
            Log::error('Failed to request email change code', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to send email change code', 500);
        }
    }

    /**
     * Confirm email change with code
     * POST /api/user/change-email/confirm
     */
    public function confirmEmailChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_email' => 'required|email|max:191',
            'code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, ['errors' => $validator->errors()]);
        }

        try {
            $user = $request->user();
            $newEmail = $request->new_email;
            $code = $request->code;

            // Validate verification code
            $verification = VerificationCode::where('email', $newEmail)
                ->where('user_id', $user->id)
                ->where('code', $code)
                ->where('type', 'email_change')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->first();

            if (!$verification) {
                return $this->error('Invalid or expired verification code', 400);
            }

            // Check if new email is still available (race condition check)
            $emailExists = \App\Models\User::where('email', $newEmail)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($emailExists) {
                return $this->error('This email address is already in use', 400);
            }

            $oldEmail = $user->email;

            // Update email
            $user->update([
                'email' => $newEmail,
                'email_verified_at' => now() // Auto-verify since they verified the code
            ]);

            // Mark code as used
            $verification->markAsUsed();

            // Log activity
            ActivityLogger::log(
                $user->id,
                'user_email_changed',
                "Email changed from {$oldEmail} to {$newEmail}",
                'App\\Models\\User',
                $user->id
            );

            Log::info('Email changed successfully', [
                'user_id' => $user->id,
                'old_email' => $oldEmail,
                'new_email' => $newEmail
            ]);

            return $this->success([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'email_verified' => $user->is_email_verified,
                    'updated_at' => $user->updated_at->toIso8601String()
                ]
            ], 'Email changed successfully');
        } catch (\Exception $e) {
            Log::error('Failed to change email', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to change email', 500);
        }
    }
}