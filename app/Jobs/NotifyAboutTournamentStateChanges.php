<?php

namespace App\Jobs;

use App\Enums\ParticipantAcceptanceState;
use App\Enums\PushNotificationType;
use App\PushNotification;
use App\Services\NotificationSender;
use App\Team;
use App\Tournament;
use App\User;
use App\UserNotificationToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyAboutTournamentStateChanges implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Tournament[] $tournaments
     */
    protected $tournaments;

    /**
     * @var string $action
     */
    protected $action;

    /**
     * NotifyAboutTournamentStateChanges constructor.
     * @param Tournament[] $tournaments
     * @param string $action
     */
    public function __construct(array $tournaments, string $action)
    {
        $this->tournaments = $tournaments;
        $this->action = $action;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $template = 'notifications.tournament.' . $this->action;
        foreach ($this->tournaments as $tournament) {
            $participants = $tournament->participants()->whereIn('status', [ParticipantAcceptanceState::ACCEPTED, ParticipantAcceptanceState::ACCEPTED_NOT_READY])->get();
            $title = 'Heads-Up';
            $body = __($template, [
                'tournament' => $tournament->title,
            ]);
            $type = PushNotificationType::TOURNAMENT;
            $image = $tournament->image;
            $userIds = [];
            foreach ($participants as $participant) {
                if ($participant->participantable_type == User::class) {
                    $userIds[] = $participant->participantable_id;
                } else if ($participant->participantable_type == Team::class) {
                    $team = Team::find($participant->participantable_id);
                    $teamUserIds = $team->players->pluck('user_id')->all();
                    $userIds = array_merge($userIds, $teamUserIds);
                }
            }
            $userIds = array_unique($userIds);

            foreach ($userIds as $userId) {
                PushNotification::query()->create([
                    'user_id' => $userId,
                    'type' => $type,
                    'title' => $title,
                    'body' => $body,
                    'image' => $image,
                    'resource_id' => $tournament->id,
                    'payload' => null,
                ]);
            }

            $notStyledBody = str_replace('**', '', $body);
            $pushService = new NotificationSender($title, $notStyledBody);
            $userIds[] = 0;
            $tokens = UserNotificationToken::query()
                ->whereIn('user_id', $userIds)
                ->get()
                ->pluck('token')
                ->all();
            if ($tokens) {
                $pushService->addTokens($tokens)->send();
            }
        }
    }
}
