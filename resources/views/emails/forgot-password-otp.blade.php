<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã OTP đặt lại mật khẩu - Keycove</title>
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
        <h1 class="title">Yêu cầu đặt lại mật khẩu</h1>

        <p class="greeting">Xin chào,</p>
        <p class="message">
            Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản Keycove của bạn. Vui lòng dùng mã OTP bên dưới để đổi mật khẩu an toàn.
        </p>

        <div class="otp-section">
            <div class="otp-label">Mã xác minh của bạn</div>
            <p class="otp-code">{{ $otp }}</p>
            <div class="expiration">
                Mã này hết hạn sau <strong>15 phút</strong>
            </div>
        </div>

        <div class="warning">
            <div class="warning-title">Lưu ý bảo mật</div>
            <p style="margin: 0;">
                Không chia sẻ mã OTP này với bất kỳ ai. Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này. Tài khoản của bạn vẫn an toàn.
            </p>
        </div>

        <p class="message" style="margin-top: 30px; margin-bottom: 0;">
            Trân trọng,<br>
            <strong>Đội ngũ Keycove</strong>
        </p>
    </div>

    <div class="footer">
        <p class="footer-text">
            {{ config('app.name') }} - Quản lý tài khoản an toàn
        </p>
        <p class="footer-text" style="font-size: 12px;">
            Đây là email bảo mật tự động. Vui lòng không trả lời.<br>
            Cần hỗ trợ? <a href="#" class="footer-link">Truy cập trung tâm hỗ trợ</a>
        </p>
    </div>
</div>
</body>
</html>
