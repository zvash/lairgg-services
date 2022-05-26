<?php

namespace App\Providers;


use App\CashOut;
use App\Events\BracketWasReleased;
use App\Events\InvitationCreated;
use App\Events\NewTournamentAnnouncementWasCreated;
use App\Events\OrderStatusWasChangedToShipped;
use App\Events\ParticipantStatusWasUpdated;
use App\Events\TeamGemsWereShared;
use App\Events\TournamentGemsWereReleased;
use App\Events\TournamentRulesWereUpdated;
use App\Listeners\EmailInvitation;
use App\Listeners\NotifyBracketWasReleased;
use App\Listeners\NotifyInvitation;
use App\Listeners\NotifyNewTournamentAnnouncementWasCreated;
use App\Listeners\NotifyParticipantJoinRequestWasAccepted;
use App\Listeners\NotifyParticipantJoinRequestWasRejected;
use App\Listeners\NotifyTeamGemsWereShared;
use App\Listeners\NotifyTournamentGemsWereReleased;
use App\Listeners\NotifyTournamentRulesWereUpdated;
use App\Listeners\SendCustomEmailVerificationNotification;
use App\Listeners\SendOrderWasShippedEmail;
use App\Listeners\UpdateBracketWithNewlyAcceptedParticipant;
use App\Observers\CashOutObserver;
use App\Observers\OrderObserver;
use App\Observers\TournamentAnnouncementObserver;
use App\Observers\TournamentObserver;
use App\Order;
use App\Tournament;
use App\TournamentAnnouncement;
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
            NotifyInvitation::class,
        ],
        ParticipantStatusWasUpdated::class => [
            UpdateBracketWithNewlyAcceptedParticipant::class,
            NotifyParticipantJoinRequestWasAccepted::class,
            NotifyParticipantJoinRequestWasRejected::class,
        ],
        OrderStatusWasChangedToShipped::class => [
            SendOrderWasShippedEmail::class
        ],
        TeamGemsWereShared::class => [
            NotifyTeamGemsWereShared::class,
        ],
        TournamentGemsWereReleased::class => [
            NotifyTournamentGemsWereReleased::class
        ],
        TournamentRulesWereUpdated::class => [
            NotifyTournamentRulesWereUpdated::class,
        ],
        BracketWasReleased::class => [
            NotifyBracketWasReleased::class,
        ],
        NewTournamentAnnouncementWasCreated::class => [
            NotifyNewTournamentAnnouncementWasCreated::class,
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
        Tournament::observe(TournamentObserver::class);
        TournamentAnnouncement::observe(TournamentAnnouncementObserver::class);
    }
}
