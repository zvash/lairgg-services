<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = $this->verificationUrl($notifiable);

        $name = $notifiable->getName();
        return (new MailMessage)
            ->subject(__('emails.verification.subject'))
            ->view('auth.mails.verify', compact('name', 'url', 'notifiable'))
            ->from(config('mail.from_address'), config('mail.from_name'))
            ->replyTo(config('mail.from_address'), config('mail.from_name'));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return config('app.url').URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'user' => $notifiable->getKey(),
                'lang' => 'en'
            ],
            false
        );
    }
}
