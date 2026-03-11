<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ __('Reset Password Notification') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #374151; margin: 0; padding: 0; background-color: #f3f4f6; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 24px; }
        .card { background: #ffffff; border-radius: 8px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { font-size: 20px; font-weight: 600; color: #111827; margin: 0 0 24px 0; }
        p { margin: 0 0 16px 0; }
        .button { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 500; margin: 16px 0; }
        .button:hover { background-color: #1d4ed8; }
        .footer { margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb; font-size: 14px; color: #6b7280; }
        .url-fallback { word-break: break-all; font-size: 12px; color: #6b7280; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <h1>{{ __('Hello!') }}</h1>

            <p>{{ __('You are receiving this email because we received a password reset request for your account.') }}</p>

            <p>{{ __('Click the button below to reset your password:') }}</p>

            <p style="margin: 24px 0;">
                <a href="{{ $url }}" class="button">{{ __('Reset Password') }}</a>
            </p>

            <p>{{ __('This password reset link will expire in :count minutes.', ['count' => $expireMinutes]) }}</p>

            <p>{{ __('If you did not request a password reset, no further action is required.') }}</p>

            <p class="url-fallback">
                {{ __('If you\'re having trouble clicking the button, copy and paste the URL below into your web browser:') }}<br>
                <a href="{{ $url }}">{{ $url }}</a>
            </p>

            <div class="footer">
                {{ __('Regards') }},<br>
                {{ $appName }}
            </div>
        </div>
    </div>
</body>
</html>
