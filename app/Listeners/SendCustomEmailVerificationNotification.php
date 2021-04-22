<?php

namespace App\Listeners;

use App\Notifications\CustomVerifyEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendCustomEmailVerificationNotification implements ShouldQueue
{
    /**
     * The name of the connection the job should be sent to.
     *
     * @var string|null
     */
    //public $connection = 'sqs';

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        if ($event->user instanceof MustVerifyEmail && ! $event->user->hasVerifiedEmail()) {
            $event->user->notify($this->notification());
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Illuminate\Auth\Events\Registered  $event
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(Registered $event, $exception)
    {
        Log::critical($exception->getMessage(), ['User' => $event->user]);
    }

    /**
     * Create verification email notification.
     *
     * @return \App\Notifications\CustomVerifyEmail
     */
    protected function notification()
    {
        return (new CustomVerifyEmail);
    }
}
