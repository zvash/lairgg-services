<?php

namespace App\Providers;


use App\Events\InvitationCreated;
use App\Events\ParticipantStatusWasUpdated;
use App\Listeners\EmailInvitation;
use App\Listeners\SendCustomEmailVerificationNotification;
use App\Listeners\UpdateBracketWithNewlyAcceptedParticipant;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendCustomEmailVerificationNotification::class,
        ],
        InvitationCreated::class => [
            EmailInvitation::class,
        ],
        ParticipantStatusWasUpdated::class => [
            UpdateBracketWithNewlyAcceptedParticipant::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
