<?php

namespace App\Jobs;

use App\Enums\PushNotificationType;
use App\Match;
use App\PushNotification;
use App\Services\NotificationSender;
use App\Team;
use App\Traits\Notifications\ParticipantHelper;
use App\Traits\Notifications\SendHelper;
use App\User;
use App\UserNotificationToken;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyAboutMatchStateChanges implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ParticipantHelper, SendHelper;

    /**
     * @var Match[] $matches
     */
    protected $matches;

    /**
     * @var string $action
     */
    protected $action;

    /**
     * NotifyAboutMatchStateChanges constructor.
     * @param Match[] $matches
     * @param string $action
     */
    public function __construct(array $matches, string $action)
    {
        $this->matches = $matches;
        $this->action = $action;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $template = 'notifications.match.' . $this->action;
        foreach ($this->matches as $match) {
            $remainedTime = '0 minute';
            if ($this->action == 'heads_up_1') {
                $remainedMinutes = Carbon::now()->diffInMinutes($match->started_at);
                $remainedTime = $this->generateRemainedTimeString($remainedMinutes);
            } else if ($this->action == 'heads_up_pre_match') {
                $remainedMinutes = $match->tournament->match_check_in_period;
                $remainedTime = $this->generateRemainedTimeString($remainedMinutes);
            }
            $participants = $match->getParticipants();
            $title = 'Heads-Up';
            $body = __($template, [
                'remained_time' => $remainedTime
            ]);
            $type = PushNotificationType::MATCH;
            $image = $match->tournament->image;
            $userIds = $this->getAllPlayersUserIdsFromParticipants($participants);
            $resourceId = $match->id;
            $this->createAndSendNotifications($userIds, $type, $title, $body, $image, $resourceId);
        }
    }

    private function generateRemainedTimeString(int $minutes)
    {
        $postfix = 'minute';
        if ($minutes > 1) {
            $postfix .= 's';
        }
        return "{$minutes} {$postfix}";
    }
}
