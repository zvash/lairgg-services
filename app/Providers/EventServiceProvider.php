<?php

namespace App\Providers;


use App\CashOut;
use App\Events\InvitationCreated;
use App\Events\OrderStatusWasChangedToShipped;
use App\Events\ParticipantStatusWasUpdated;
use App\Events\TeamGemsWereShared;
use App\Listeners\EmailInvitation;
use App\Listeners\NotifyTeamGemsWereShared;
use App\Listeners\SendCustomEmailVerificationNotification;
use App\Listeners\SendOrderWasShippedEmail;
use App\Listeners\UpdateBracketWithNewlyAcceptedParticipant;
use App\Observers\CashOutObserver;
use App\Observers\OrderObserver;
use App\Order;
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
        OrderStatusWasChangedToShipped::class => [
            SendOrderWasShippedEmail::class
        ],
        TeamGemsWereShared::class => [
            NotifyTeamGemsWereShared::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        CashOut::observe(CashOutObserver::class);
        Order::observe(OrderObserver::class);
    }
}
