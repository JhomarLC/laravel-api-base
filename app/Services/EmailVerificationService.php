<?php

namespace App\Services;

use App\Models\EmailVerificationOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmailVerificationService
{
    public function sendOtp(User $user): void
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailVerificationOtp::updateOrCreate(
            ['email' => $user->email],
            [
                'otp_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes(15),
            ]
        );

        $user->notify(new \App\Notifications\VerifyEmailOtpnotification($otp));
    }

    public function verify(string $email, string $otp): ?User
    {
        $user = User::where('email', $email)->first();

        if (! $user || $user->hasVerifiedEmail()) {
            return null;
        }

        $record = EmailVerificationOtp::where('email', $email)
            ->where('expires_at', '>', now())
            ->first();

        if (! $record || ! Hash::check($otp, $record->otp_hash)) {
            return null;
        }

        $user->markEmailAsVerified();
        $record->delete();

        return $user;
    }

    public function resend(string $email): void
    {
        $user = User::where('email', $email)->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            $this->sendOtp($user);
        }
    }
}