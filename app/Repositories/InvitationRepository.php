<?php

namespace App\Repositories;


use App\Events\InvitationCreated;
use App\Invitation;
use App\Team;
use App\Tournament;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class InvitationRepository extends BaseRepository
{
    protected $modelClass = Invitation::class;

    /**
     * @param Tournament $tournament
     * @param string $identifier
     * @param User $user
     * @param User|null $invitee
     */
    public function createTournamentInvitation(Tournament $tournament, string $identifier, User $user, ?User $invitee)
    {
        $email = $invitee ? $invitee->email : $identifier;
        try {
            $invitation = Invitation::create([
                'invited_by' => $user->id,
                'organization_id' => $tournament->organization->id,
                'invite_aware_type' => Tournament::class,
                'invite_aware_id' => $tournament->id,
                'email' => $email,
            ]);
            $this->fireCreationEvents($invitation, $invitee);
        } catch (QueryException $exception) {
            $invitation = Invitation::where('invited_by', $user->id)
                ->where('invite_aware_type', Tournament::class)
                ->where('invite_aware_id', $tournament->id)
                ->where('email', $email)
                ->first();
            if ($invitation) {
                $this->fireCreationEvents($invitation, $invitee);
            }
        }
    }

    /**
     * @param Team $team
     * @param string $identifier
     * @param User $user
     * @param User|null $invitee
     */
    public function createTeamInvitation(Team $team, string $identifier, User $user, ?User $invitee)
    {
        $email = $invitee ? $invitee->email : $identifier;
        try {
            $invitation = Invitation::create([
                'invited_by' => $user->id,
                'organization_id' => null,
                'invite_aware_type' => Team::class,
                'invite_aware_id' => $team->id,
                'email' => $email,
            ]);
            $this->fireCreationEvents($invitation, $invitee);
        } catch (QueryException $exception) {
            $invitation = Invitation::where('invited_by', $user->id)
                ->where('invite_aware_type', Team::class)
                ->where('invite_aware_id', $team->id)
                ->where('email', $email)
                ->first();
            if ($invitation) {
                $this->fireCreationEvents($invitation, $invitee);
            }
        }
    }

    public function flashInvitations(User $user)
    {
        $invitationTypeMap = [
            Tournament::class => [
                'type' => 'tournament',
                'image' => 'image',
                'cover' => 'cover',
            ],
            Team::class => [
                'type' => 'team',
                'image' => 'logo',
                'cover' => 'cover',
            ],
        ];
        $groupedInvitations = Invitation::query();
        $groupedInvitations = $groupedInvitations->groupBy(['invite_aware_type', 'invite_aware_id', 'email'])
            ->selectRaw('invite_aware_type, invite_aware_id, email, GROUP_CONCAT(invited_by) as inviters, GROUP_CONCAT(id) as ids')
            ->where('email', $user->email)
            ->whereNull('accepted')
            ->where('is_flashed', false)
            ->get()
            ->all();
        $token = Str::orderedUuid();
        $flashInvitations = [
            'token' => $token,
            'invitations' => [],
        ];
        foreach ($groupedInvitations as $groupedInvitation) {
            Invitation::whereIn('id', explode(',', $groupedInvitation->ids))
                ->update(['token' => $token]);
            $invitersRecords = User::whereIn('id', explode(',', $groupedInvitation->inviters))->pluck('avatar', 'username')->all();
            $inviters = array_keys($invitersRecords);
            $lastInviter = end($inviters);
            unset($inviters[count($inviters) - 1]);
            $usernames = implode(',', $inviters);
            if ($usernames) {
                $usernames .= ' and ' . $lastInviter;
            } else {
                $usernames = $lastInviter;
            }
            $withAvatar = [];
            foreach ($invitersRecords as $username => $avatar) {
                $withAvatar[] = [
                    'username' => $username,
                    'avatar' => $avatar
                ];
            }
            $invitedToObject = $groupedInvitation->invite_aware_type::find($groupedInvitation->invite_aware_id);
            $imageField = $invitationTypeMap[$groupedInvitation->invite_aware_type]['image'];
            $coverField = $invitationTypeMap[$groupedInvitation->invite_aware_type]['cover'];
            $tournamentDetails = null;
            $teamDetails = null;
            if ($invitedToObject) {
                $title = $invitedToObject->title;
                if ($groupedInvitation->invite_aware_type == Tournament::class) {
                    $organizationTitle = $invitedToObject->organization->title;
                    $organizationLogo = $invitedToObject->organization->logo;
                    $gameTitle = $invitedToObject->game->title;
                    $prizeValue = null;
                    $prizeType = null;
                    $prize = $invitedToObject->prizes()->where('rank', 1)->first();
                    if ($prize) {
                        $prizeValue = $prize->value;
                        $prizeType = $prize->valueType->title;
                    }
                    $tournamentDetails = [
                        'organization' => $organizationTitle,
                        'organization_logo' => $organizationLogo,
                        'game' => $gameTitle,
                        'prize_value' => $prizeValue,
                        'prize_type' => $prizeType,
                        'starts_at' => $invitedToObject->startted_at
                    ];
                } else {
                    $gameTitle = $invitedToObject->game->title;
                    $teamDetails = [
                        'game' => $gameTitle,
                    ];
                }
                $flashInvitations['invitations'][] = [
                    'invitation_type' => $invitationTypeMap[$groupedInvitation->invite_aware_type]['type'],
                    'invitation_id' => $groupedInvitation->invite_aware_id,
                    'image' => $invitedToObject->$imageField,
                    'cover' => $invitedToObject->$coverField,
                    'title' => $title,
                    'inviters' => [
                        'usernames' => $usernames,
                        'with_avatar' => $withAvatar
                    ],
                    'tournament_details' => $tournamentDetails,
                    'team_details' => $teamDetails,
                ];
            }

        }
        return $flashInvitations;
    }

    /**
     * @param User $user
     * @param string $token
     */
    public function flashedOnce(User $user, string $token)
    {
        Invitation::where('email', $user->email)
            ->where('token', $token)
            ->update(['is_flashed' => true]);
    }

    /**
     * @param Invitation $invitation
     * @param User|null $invitee
     */
    private function fireCreationEvents(Invitation $invitation, ?User $invitee)
    {
        event(new InvitationCreated($invitation, $invitee));
    }
}