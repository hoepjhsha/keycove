<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP - Keycove</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #F3F4F6;
            margin: 0;
            padding: 40px 20px;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 560px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #E5E7EB;
        }
        .top-bar {
            height: 6px;
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            width: 100%;
        }
        .header {
            padding: 30px 40px 10px;
            text-align: center;
        }
        .brand {
            font-size: 24px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .brand span {
            color: #4F46E5;
        }
        .content {
            padding: 20px 40px 40px;
        }
        .title {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 20px;
            text-align: center;
        }
        .greeting {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 15px;
            color: #111827;
        }
        .message {
            font-size: 15px;
            color: #4B5563;
            margin-bottom: 25px;
        }
        .otp-section {
            background-color: #F5F3FF;
            border: 1px solid #EDE9FE;
            padding: 30px 20px;
            margin: 30px 0;
            border-radius: 12px;
            text-align: center;
        }
        .otp-label {
            font-size: 13px;
            font-weight: 600;
            color: #6D28D9;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }
        .otp-code {
            font-size: 42px;
            font-weight: 800;
            letter-spacing: 12px;
            color: #4F46E5;
            font-family: 'Courier New', monospace;
            margin: 0;
            padding-left: 12px; /* Cân bằng letter-spacing */
        }
        .expiration {
            font-size: 13px;
            color: #6B7280;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .warning {
            background-color: #FFFBEB;
            border: 1px solid #FEF3C7;
            padding: 16px 20px;
            margin: 25px 0;
            border-radius: 8px;
            font-size: 14px;
            color: #92400E;
        }
        .warning-title {
            font-weight: 600;
            margin-bottom: 4px;
            color: #B45309;
        }
        .footer {
            background-color: #F9FAFB;
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }
        .footer-text {
            font-size: 13px;
            color: #6B7280;
            margin: 0 0 10px 0;
        }
        .footer-link {
            color: #4F46E5;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar"></div>

    <div class="header">
        <p class="brand">Keycove<span>.</span></p>
    </div>

    <div class="content">
        <h1 class="title">🔐 Password Reset Request</h1>

        <p class="greeting">Hi there,</p>
        <p class="message">
            We received a request to reset the password for your Keycove account. Please use the One-Time Password (OTP) below to securely change your password.
        </p>

        <div class="otp-section">
            <div class="otp-label">Your Verification Code</div>
            <p class="otp-code">{{ $otp }}</p>
            <div class="expiration">
                ⏳ This code expires in <strong>15 minutes</strong>
            </div>
        </div>

        <div class="warning">
            <div class="warning-title">⚠️ Security Notice</div>
            <p style="margin: 0;">
                Never share this OTP with anyone. If you didn't request a password reset, you can safely ignore this email. Your account remains secure.
            </p>
        </div>

        <p class="message" style="margin-top: 30px; margin-bottom: 0;">
            Best regards,<br>
            <strong>The Keycove Team</strong>
        </p>
    </div>

    <div class="footer">
        <p class="footer-text">
            {{ config('app.name') }} - Secure Password Management
        </p>
        <p class="footer-text" style="font-size: 12px;">
            This is an automated security email. Please do not reply.<br>
            Need help? <a href="#" class="footer-link">Visit our Support Center</a>
        </p>
    </div>
</div>
</body>
</html>
