<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class AdminStaffVerifyEmail extends Notification
{
    use Queueable;

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
        $verificationUrl = $this->verificationUrl($notifiable);
        $otp = (string) random_int(100000, 999999);

        // Store OTP in cache for 60 minutes
        Cache::put('email_verification_otp_' . $notifiable->getKey(), $otp, now()->addMinutes(60));

        return (new MailMessage)
            ->subject('Verify Your Email Address - CAWS Portal')
            ->view('emails.verify-email', [
                'user' => $notifiable,
                'url'  => $verificationUrl,
                'otp'  => $otp,
            ]);
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl(object $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
