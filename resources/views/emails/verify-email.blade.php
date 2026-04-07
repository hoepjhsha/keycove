<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - Keycove</title>
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
        .icon-wrapper {
            width: 64px;
            height: 64px;
            background-color: #EEF2FF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
        }
        .title {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
            text-align: center;
        }
        .message {
            font-size: 15px;
            color: #4B5563;
            margin-bottom: 30px;
            text-align: center;
        }
        .cta-container {
            text-align: center;
            margin: 35px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: #ffffff;
            padding: 16px 36px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
        }
        .alternative-box {
            background-color: #F9FAFB;
            border: 1px dashed #D1D5DB;
            padding: 20px;
            margin: 30px 0;
            border-radius: 8px;
            text-align: center;
        }
        .alternative-label {
            font-size: 12px;
            font-weight: 600;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .verification-link {
            font-size: 13px;
            color: #4F46E5;
            word-break: break-all;
            margin: 0;
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
        <div class="icon-wrapper">✉️</div>
        <h1 class="title">Verify Your Email Address</h1>

        <p class="message">
            Welcome to Keycove! To complete your registration and unlock full access to your secure account, please verify your email address by clicking the button below.
        </p>

        <div class="cta-container">
            <a href="{{ $verificationLink }}" class="cta-button">Verify Email Address</a>
        </div>

        <div class="alternative-box">
            <div class="alternative-label">Or copy and paste this link into your browser:</div>
            <p class="verification-link">{{ $verificationLink }}</p>
        </div>

        <p class="message" style="margin-top: 30px; margin-bottom: 0; font-size: 14px;">
            This verification link is valid for <strong>24 hours</strong>.<br>
            If you didn't create an account, you can safely delete this email.
        </p>
    </div>

    <div class="footer">
        <p class="footer-text">
            {{ config('app.name') }} - Secure Password Management
        </p>
        <p class="footer-text" style="font-size: 12px;">
            Automated email. Please do not reply.<br>
            Questions? <a href="#" class="footer-link">Check our Help Center</a>
        </p>
    </div>
</div>
</body>
</html>
