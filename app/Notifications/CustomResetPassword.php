<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends ResetPassword implements ShouldQueue
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
        return (new MailMessage)
            ->subject(__('emails.reset.subject'))
            ->markdown('auth.mails.reset', [
                'url' => $this->url($notifiable),
                'expire' => $this->expire(),
                'notifiable' => $notifiable,
            ]);
    }

    /**
     * Get The absolute URL for reset mail button.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string
     */
    private function url($notifiable)
    {
        $route = route(
            'users.password.reset.form',
            [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
                'lang' => 'en',
            ],
            false
        );

        return url(config('app.url').$route);
    }

    /**
     * Get Expire time in minutes.
     *
     * @return mixed
     */
    private function expire()
    {
        return config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
    }
}
