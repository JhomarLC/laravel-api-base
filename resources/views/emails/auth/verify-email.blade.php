<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ __('Verify Your Email Address') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #374151; margin: 0; padding: 0; background-color: #f3f4f6; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 24px; }
        .card { background: #ffffff; border-radius: 8px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { font-size: 20px; font-weight: 600; color: #111827; margin: 0 0 24px 0; }
        p { margin: 0 0 16px 0; }
        .otp { font-size: 28px; font-weight: 600; letter-spacing: 0.25em; color: #111827; padding: 16px 24px; background: #f3f4f6; border-radius: 8px; display: inline-block; margin: 16px 0; }
        .footer { margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb; font-size: 14px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <h1>{{ __('Hello :name!', ['name' => $user->name]) }}</h1>

            <p>{{ __('Your email verification code is:') }}</p>

            <p class="otp">{{ $otp }}</p>

            <p>{{ __('This code will expire in :count minutes.', ['count' => $expiryMinutes]) }}</p>

            <p>{{ __('If you did not request this, please ignore this email.') }}</p>

            <div class="footer">
                {{ __('Regards') }},<br>
                {{ $appName }}
            </div>
        </div>
    </div>
</body>
</html>
