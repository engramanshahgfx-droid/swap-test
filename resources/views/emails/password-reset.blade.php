{{-- resources/views/emails/password-reset.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #1e2235;
        }
        .container {
            max-width: 580px;
            margin: 40px auto;
            background: #ffffff;
            padding: 40px 48px;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            border: 1px solid #e9edf4;
        }
        .header {
            text-align: center;
            padding-bottom: 24px;
            border-bottom: 2px solid #f0f2f7;
        }
        .header .logo {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        .header .logo span {
            color: #0ea5e9;
        }
        .header .sub {
            font-size: 14px;
            color: #94a3b8;
            margin-top: 4px;
        }
        .content {
            padding: 32px 0;
            line-height: 1.7;
        }
        .content h2 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .content p {
            font-size: 15px;
            color: #374151;
            margin-bottom: 16px;
        }
        .content .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
        }
        .button-container {
            text-align: center;
            margin: 32px 0;
        }
        .reset-button {
            display: inline-block;
            padding: 14px 40px;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.35);
            transition: all 0.2s ease;
        }
        .reset-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.45);
        }
        .divider {
            border: none;
            border-top: 2px solid #f0f2f7;
            margin: 24px 0;
        }
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 20px;
            margin: 20px 0;
            font-size: 14px;
            color: #475569;
        }
        .info-box strong {
            color: #0f172a;
        }
        .warning-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 14px 18px;
            margin: 16px 0;
            font-size: 14px;
            color: #78350f;
        }
        .footer {
            text-align: center;
            padding-top: 24px;
            border-top: 2px solid #f0f2f7;
            font-size: 13px;
            color: #94a3b8;
        }
        .footer a {
            color: #0ea5e9;
            text-decoration: none;
        }
        .footer .meta {
            margin-top: 8px;
            font-size: 12px;
            color: #cbd5e1;
        }
        @media (max-width: 640px) {
            .container {
                padding: 24px 20px;
                margin: 20px 12px;
            }
            .reset-button {
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">✈️ <span>Crew</span>Swap</div>
            <div class="sub">Flight Crew Scheduling &amp; Swap Platform</div>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>🔐 Reset Your Password</h2>

            <p class="greeting">Hello {{ $user->full_name ?? $user->name ?? 'User' }},</p>

            <p>We received a request to reset the password for your <strong>{{ config('app.name') }}</strong> account.</p>

            <p>Click the button below to securely reset your password. This link will expire in <strong>24 hours</strong>.</p>

            <!-- Reset Button -->
            <div class="button-container">
                <a href="{{ $resetUrl ?? url('/reset-password?token=' . $token . '&email=' . urlencode($user->email)) }}"
                   class="reset-button">
                    🔑 Reset Password
                </a>
            </div>

            <p style="font-size: 14px; color: #64748b;">
                If the button doesn't work, copy and paste this URL into your browser:
            </p>
            <div style="background: #f1f5f9; padding: 12px 16px; border-radius: 8px; font-size: 13px; word-break: break-all; color: #0f172a; margin-bottom: 16px;">
                {{ $resetUrl ?? url('/reset-password?token=' . $token . '&email=' . urlencode($user->email)) }}
            </div>

            <div class="warning-box">
                ⚠️ <strong>Security Alert:</strong> If you didn't request this password reset, please ignore this email.
                Your account remains secure.
            </div>

            <hr class="divider">

            <div class="info-box">
                <strong>🔒 Security Tips:</strong>
                <ul style="margin: 8px 0 0 0; padding-left: 20px; color: #475569;">
                    <li>Never share your password with anyone</li>
                    <li>{{ config('app.name') }} will never ask for your password via email</li>
                    <li>Use a strong, unique password for your account</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
            <p style="font-size: 13px; color: #94a3b8;">
                This email was sent to <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
            </p>
            <div class="meta">
                <span>📧 {{ config('app.name') }} Support: support@crewswap.com</span>
            </div>
        </div>
    </div>
</body>
</html>
