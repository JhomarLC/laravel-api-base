<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailOtpnotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $otp,
    )
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $expiryMinutes = 15;
        $view = config('auth.verify_email_otp_view');

        $mail = (new MailMessage)
            ->subject(__('Verify Your Email Address'));

        if (! empty($view) && view()->exists($view)) {
            return $mail->view($view, [
                'otp' => $this->otp,
                'user' => $notifiable,
                'expiryMinutes' => $expiryMinutes,
                'appName' => config('app.name'),
            ]);
        }

        return $mail
            ->greeting(__('Hello :name!', ['name' => $notifiable->name]))
            ->line(__('Your email verification code is:'))
            ->line('**' . $this->otp . '**')
            ->line(__('This code will expire in :count minutes.', ['count' => $expiryMinutes]))
            ->line(__('If you did not request this, please ignore this email.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
