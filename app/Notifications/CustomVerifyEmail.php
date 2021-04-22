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

        return (new MailMessage)
            ->subject(__('emails.verification.subject'))
            ->markdown('auth.mails.verify', compact('notifiable', 'url'))
            ->from(env('MAIL_FROM_ADDRESS', 'info@lair.gg'), env('MAIL_FROM_NAME', 'LAIRGG'))
            ->replyTo(env('MAIL_FROM_ADDRESS', 'info@lair.gg'), env('MAIL_FROM_NAME', 'LAIRGG'));
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
