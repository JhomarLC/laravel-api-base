<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
        $url = $frontendUrl . '/reset-password?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $expireMinutes = config('auth.passwords.users.expire', 60);
        $view = config('auth.password_reset_email_view');

        $mail = (new MailMessage)
            ->subject(__('Reset Password Notification'));

        if (! empty($view) && view()->exists($view)) {
            return $mail->view($view, [
                'url' => $url,
                'user' => $notifiable,
                'expireMinutes' => $expireMinutes,
                'appName' => config('app.name'),
            ]);
        }

        return $mail
            ->line(__('You are receiving this email because we received a password reset request for your account.'))
            ->action(__('Reset Password'), $url)
            ->line(__('This password reset link will expire in :count minutes.', ['count' => $expireMinutes]))
            ->line(__('If you did not request a password reset, no further action is required.'));
    }
}
