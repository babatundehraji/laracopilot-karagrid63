<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Sucheus' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-body p {
            margin-bottom: 16px;
            color: #555555;
            font-size: 16px;
        }
        .email-body h2 {
            color: #333333;
            font-size: 22px;
            margin-bottom: 16px;
        }
        .email-body h3 {
            color: #444444;
            font-size: 18px;
            margin-bottom: 12px;
        }
        .email-body ul, .email-body ol {
            margin-bottom: 16px;
            padding-left: 30px;
        }
        .email-body li {
            margin-bottom: 8px;
            color: #555555;
        }
        .button {
            display: inline-block;
            padding: 14px 30px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
        }
        .code-box {
            background-color: #f8f9fa;
            border: 2px solid #6366f1;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 24px 0;
        }
        .code-box .code {
            font-size: 32px;
            font-weight: 700;
            color: #6366f1;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .code-box .code-label {
            font-size: 14px;
            color: #666666;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-box {
            background-color: #e0e7ff;
            border-left: 4px solid #6366f1;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box p {
            margin: 0;
            color: #4338ca;
        }
        .warning-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning-box p {
            margin: 0;
            color: #92400e;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px 20px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .email-footer p {
            margin: 8px 0;
            color: #6b7280;
            font-size: 14px;
        }
        .email-footer a {
            color: #6366f1;
            text-decoration: none;
        }
        .email-footer a:hover {
            text-decoration: underline;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
        }
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            .email-body {
                padding: 30px 20px;
            }
            .email-header h1 {
                font-size: 24px;
            }
            .code-box .code {
                font-size: 28px;
                letter-spacing: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Sucheus</h1>
        </div>
        
        <div class="email-body">
            {!! $msg !!}
        </div>
        
        <div class="email-footer">
            <p><strong>Sucheus</strong></p>
            <p>Your trusted service marketplace</p>
            <div class="social-links">
                <a href="#">Facebook</a> |
                <a href="#">Twitter</a> |
                <a href="#">Instagram</a> |
                <a href="#">LinkedIn</a>
            </div>
            <p style="margin-top: 20px; color: #9ca3af; font-size: 12px;">
                This email was sent by Sucheus. If you have any questions, please contact our support team.
            </p>
            <p style="color: #9ca3af; font-size: 12px;">
                © {{ date('Y') }} Sucheus. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
