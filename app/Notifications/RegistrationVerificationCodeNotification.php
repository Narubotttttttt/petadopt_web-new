<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationVerificationCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $otp,
        public string $name = 'User'
    ) {}

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
        $dummyUser = (object) ['name' => $this->name];

        return (new MailMessage)
            ->subject('Your CAWS Registration Verification Code')
            ->view('emails.verify-email', [
                'user'              => $dummyUser,
                'url'               => url('/register'),
                'otp'               => $this->otp,
                'isRegistrationOtp' => true,
            ]);
    }
}
