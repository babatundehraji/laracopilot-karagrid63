<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLoginLog;
use App\Models\VerificationCode;
use App\Services\ActivityLogger;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Request email verification code
     * POST /api/auth/request-email-code
     */
    public function requestEmailCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:191'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $email = $request->email;

        try {
            // Generate verification code
            $verification = VerificationCode::generate(
                $email,
                'email_verification',
                null,
                15 // 15 minutes expiry
            );

            // Send email with verification code
            EmailService::sendVerificationCode(
                $email,
                $verification->code,
                'email_verification',
                15,
                true // queue
            );

            Log::info('Email verification code requested', ['email' => $email]);

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to your email',
                'data' => [
                    'email' => $email,
                    'expires_in_minutes' => 15
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to request email code', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code',
                'data' => null
            ], 500);
        }
    }

    /**
     * Verify email code
     * POST /api/auth/verify-email-code
     */
    public function verifyEmailCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:191',
            'code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $email = $request->email;
        $code = $request->code;

        try {
            $verification = VerificationCode::findValidCode(
                $email,
                $code,
                'email_verification'
            );

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired verification code',
                    'data' => null
                ], 400);
            }

            // Mark code as used
            $verification->markAsUsed();

            Log::info('Email code verified', ['email' => $email]);

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully',
                'data' => [
                    'email' => $email,
                    'verified' => true
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to verify email code', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify code',
                'data' => null
            ], 500);
        }
    }

    /**
     * Register new user
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'verification_code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $email = $request->email;
        $code = $request->verification_code;

        try {
            // Verify email code first
            $verification = VerificationCode::findValidCode(
                $email,
                $code,
                'email_verification'
            );

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired verification code',
                    'data' => null
                ], 400);
            }

            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $email,
                'password' => Hash::make($request->password),
                'role' => 'customer',
                'status' => 'active',
                'email_verified_at' => now(),
                'timezone' => 'Africa/Lagos'
            ]);

            // Mark verification code as used
            $verification->markAsUsed();

            // Generate Sanctum token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Update last login
            $user->updateLastLogin();

            // Log activity
            ActivityLogger::logRegistration($user->id, $user->email);

            // Send welcome email
            EmailService::sendWelcome($user->email, $user->full_name, true);

            Log::info('User registered', ['user_id' => $user->id, 'email' => $email]);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'full_name' => $user->full_name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status,
                        'email_verified' => $user->is_email_verified,
                        'avatar' => $user->avatar,
                        'created_at' => $user->created_at->toIso8601String()
                    ],
                    'token' => $token
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to register user', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'data' => null
            ], 500);
        }
    }

    /**
     * Login user
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $email = $request->email;
        $password = $request->password;

        try {
            // Find user
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'data' => null
                ], 401);
            }

            // Reject admin users
            if ($user->role === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin users cannot login via API',
                    'data' => null
                ], 403);
            }

            // Check password
            if (!Hash::check($password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'data' => null
                ], 401);
            }

            // Check if user is suspended
            if ($user->status === 'suspended') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account suspended. Please contact support.',
                    'data' => null
                ], 403);
            }

            // Generate Sanctum token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Update last login
            $user->updateLastLogin();

            // Log user login in database
            UserLoginLog::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at' => now(),
                'status' => 'success'
            ]);

            // Log activity
            ActivityLogger::logLogin($user->id, $user->email);

            Log::info('User logged in', ['user_id' => $user->id, 'email' => $email]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'full_name' => $user->full_name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status,
                        'email_verified' => $user->is_email_verified,
                        'phone_verified' => $user->is_phone_verified,
                        'avatar' => $user->avatar,
                        'last_login' => $user->last_login
                    ],
                    'token' => $token
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to login user', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'data' => null
            ], 500);
        }
    }

    /**
     * Request password reset code
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:191'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $email = $request->email;

        try {
            // Check if user exists
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Don't reveal if user exists or not (security)
                return response()->json([
                    'success' => true,
                    'message' => 'If this email exists, a password reset code has been sent',
                    'data' => ['email' => $email]
                ], 200);
            }

            // Generate password reset code
            $verification = VerificationCode::generate(
                $email,
                'password_reset',
                $user->id,
                15 // 15 minutes expiry
            );

            // Send email with reset code
            EmailService::sendVerificationCode(
                $email,
                $verification->code,
                'password_reset',
                15,
                true // queue
            );

            Log::info('Password reset code requested', ['email' => $email]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset code sent to your email',
                'data' => [
                    'email' => $email,
                    'expires_in_minutes' => 15
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to request password reset', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send password reset code',
                'data' => null
            ], 500);
        }
    }

    /**
     * Reset password with code
     * POST /api/auth/reset-password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:191',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        $email = $request->email;
        $code = $request->code;
        $password = $request->password;

        try {
            // Verify reset code
            $verification = VerificationCode::findValidCode(
                $email,
                $code,
                'password_reset'
            );

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired reset code',
                    'data' => null
                ], 400);
            }

            // Find user
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null
                ], 404);
            }

            // Update password
            $user->update([
                'password' => Hash::make($password)
            ]);

            // Mark verification code as used
            $verification->markAsUsed();

            // Revoke all existing tokens (force re-login)
            $user->tokens()->delete();

            // Log activity
            ActivityLogger::logPasswordReset($user->id, $user->email);

            Log::info('Password reset successful', ['user_id' => $user->id, 'email' => $email]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successful. Please login with your new password.',
                'data' => ['email' => $email]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to reset password', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password',
                'data' => null
            ], 500);
        }
    }

    /**
     * Logout user (revoke current token)
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'data' => null
                ], 401);
            }

            // Log activity before revoking token
            ActivityLogger::logLogout($user->id, $user->email);

            // Revoke current token only
            $request->user()->currentAccessToken()->delete();

            Log::info('User logged out', ['user_id' => $user->id, 'email' => $user->email]);

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to logout user', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'data' => null
            ], 500);
        }
    }
}