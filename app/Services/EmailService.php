<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Mail\GenericMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send an email (queued or immediate)
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $msg HTML message body
     * @param bool $queue Whether to queue the email (default: true)
     * @return bool Success status
     */
    public static function send(string $to, string $subject, string $msg, bool $queue = true): bool
    {
        try {
            if ($queue) {
                // Dispatch to queue for background processing
                SendEmailJob::dispatch($to, $subject, $msg);
                
                Log::info('Email queued for sending', [
                    'to' => $to,
                    'subject' => $subject,
                    'queued' => true
                ]);
            } else {
                // Send immediately
                Mail::to($to)->send(new GenericMail($subject, $msg));
                
                Log::info('Email sent immediately', [
                    'to' => $to,
                    'subject' => $subject,
                    'queued' => false
                ]);
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send/queue email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'queued' => $queue
            ]);
            
            return false;
        }
    }

    /**
     * Send verification code email
     *
     * @param string $to Recipient email
     * @param string $code Verification code
     * @param string $type Type of verification (email_verification, password_reset)
     * @param int $expiryMinutes Minutes until code expires
     * @param bool $queue Whether to queue
     * @return bool
     */
    public static function sendVerificationCode(
        string $to,
        string $code,
        string $type = 'email_verification',
        int $expiryMinutes = 15,
        bool $queue = true
    ): bool {
        $subjects = [
            'email_verification' => 'Verify Your Email Address',
            'password_reset' => 'Reset Your Password'
        ];

        $messages = [
            'email_verification' => '
                <h2>Welcome to Sucheus!</h2>
                <p>Thank you for signing up. Please verify your email address to get started.</p>
                <div class="code-box">
                    <div class="code-label">Your Verification Code</div>
                    <div class="code">' . $code . '</div>
                </div>
                <div class="info-box">
                    <p><strong>This code will expire in ' . $expiryMinutes . ' minutes.</strong></p>
                </div>
                <p>Enter this code in the verification screen to complete your registration.</p>
                <div class="warning-box">
                    <p>If you did not create an account, please ignore this email or contact our support team.</p>
                </div>
            ',
            'password_reset' => '
                <h2>Reset Your Password</h2>
                <p>We received a request to reset your password. Use the code below to proceed:</p>
                <div class="code-box">
                    <div class="code-label">Password Reset Code</div>
                    <div class="code">' . $code . '</div>
                </div>
                <div class="info-box">
                    <p><strong>This code will expire in ' . $expiryMinutes . ' minutes.</strong></p>
                </div>
                <p>Enter this code in the password reset screen to create a new password.</p>
                <div class="warning-box">
                    <p><strong>Security Notice:</strong> If you did not request a password reset, please ignore this email. Your account remains secure.</p>
                </div>
            '
        ];

        $subject = $subjects[$type] ?? 'Verification Code';
        $message = $messages[$type] ?? '<p>Your verification code is: <strong>' . $code . '</strong></p>';

        return self::send($to, $subject, $message, $queue);
    }

    /**
     * Send welcome email
     *
     * @param string $to
     * @param string $name
     * @param bool $queue
     * @return bool
     */
    public static function sendWelcome(string $to, string $name, bool $queue = true): bool
    {
        $subject = 'Welcome to Sucheus!';
        $message = '
            <h2>Welcome, ' . htmlspecialchars($name) . '! 🎉</h2>
            <p>We\'re thrilled to have you join Sucheus, your trusted service marketplace.</p>
            
            <h3>Get Started</h3>
            <ul>
                <li>Browse thousands of professional services</li>
                <li>Book services with just a few clicks</li>
                <li>Track your orders in real-time</li>
                <li>Connect with verified service providers</li>
            </ul>
            
            <div class="info-box">
                <p>Need help getting started? Our support team is here 24/7 to assist you.</p>
            </div>
            
            <p>Thank you for choosing Sucheus. We look forward to serving you!</p>
        ';

        return self::send($to, $subject, $message, $queue);
    }

    /**
     * Send order confirmation email
     *
     * @param string $to
     * @param array $orderDetails
     * @param bool $queue
     * @return bool
     */
    public static function sendOrderConfirmation(string $to, array $orderDetails, bool $queue = true): bool
    {
        $subject = 'Order Confirmed - #' . $orderDetails['order_number'];
        $message = '
            <h2>Order Confirmed! ✅</h2>
            <p>Your order has been successfully placed and confirmed.</p>
            
            <h3>Order Details</h3>
            <ul>
                <li><strong>Order Number:</strong> #' . htmlspecialchars($orderDetails['order_number']) . '</li>
                <li><strong>Service:</strong> ' . htmlspecialchars($orderDetails['service_name']) . '</li>
                <li><strong>Provider:</strong> ' . htmlspecialchars($orderDetails['vendor_name']) . '</li>
                <li><strong>Amount:</strong> ₦' . number_format($orderDetails['amount'], 2) . '</li>
                <li><strong>Date:</strong> ' . htmlspecialchars($orderDetails['date']) . '</li>
            </ul>
            
            <div class="info-box">
                <p>The service provider will contact you shortly to confirm the appointment details.</p>
            </div>
            
            <p>You can track your order status in your dashboard.</p>
        ';

        return self::send($to, $subject, $message, $queue);
    }

    /**
     * Send custom notification
     *
     * @param string $to
     * @param string $title
     * @param string $message
     * @param bool $queue
     * @return bool
     */
    public static function sendNotification(string $to, string $title, string $message, bool $queue = true): bool
    {
        $subject = $title;
        $msg = '
            <h2>' . htmlspecialchars($title) . '</h2>
            <p>' . nl2br(htmlspecialchars($message)) . '</p>
        ';

        return self::send($to, $subject, $msg, $queue);
    }
}