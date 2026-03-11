<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Email Previews (local only)
|--------------------------------------------------------------------------
|
| Visit /mail/preview/password-reset or /mail/preview/verify-email to see
| how the emails render. Only available when APP_DEBUG=true.
|
*/
if (config('app.debug')) {
    Route::get('/mail/preview/password-reset', function () {
        $user = User::factory()->make(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
        $url = config('app.frontend_url') . '/reset-password?token=abc123&email=jane@example.com';

        $view = config('auth.password_reset_email_view') ?: 'emails.auth.password-reset';

        return view($view, [
            'url' => $url,
            'user' => $user,
            'expireMinutes' => config('auth.passwords.users.expire', 60),
            'appName' => config('app.name'),
        ]);
    });

    Route::get('/mail/preview/verify-email', function () {
        $user = User::factory()->make(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        $view = config('auth.verify_email_otp_view') ?: 'emails.auth.verify-email';

        return view($view, [
            'otp' => '123456',
            'user' => $user,
            'expiryMinutes' => 15,
            'appName' => config('app.name'),
        ]);
    });
}