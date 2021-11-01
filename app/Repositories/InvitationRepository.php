<?php

namespace App\Repositories;


use App\Enums\ParticipantAcceptanceState;
use App\Events\InvitationCreated;
use App\Events\ParticipantStatusWasUpdated;
use App\Invitation;
use App\Participant;
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

    /**
     * @param User $user
     * @return int
     */
    public function countInvitations(User $user)
    {
        return Invitation::query()
            ->where('email', $user->email)
            ->whereNull('accepted')
            ->count();
    }

    /**
     * @param User $user
     * @param string $type
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function unansweredInvitations(User $user, string $type)
    {
        $typeMap = [
            'teams' => Team::class,
            'tournaments' => Tournament::class,
        ];
        if (array_key_exists($type, $typeMap)) {
            $type = $typeMap[$type];
        }
        $relations = ['inviter', 'inviteAware'];
        if ($type == Team::class) {
            $relations[] = 'inviteAware.players';
        } else if ($type == Tournament::class) {
            $relations[] = 'inviteAware.organization';
        }
        return Invitation::query()
            ->where('email', $user->email)
            ->where('invite_aware_type', $type)
            ->whereNull('accepted')
            ->with($relations)
            ->paginate(10);
    }

    /**
     * @param User $user
     * @return array
     */
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
                        'starts_at' => $invitedToObject->started_at,
                        'participantables' => $this->participantablesForTournament($user, $invitedToObject),
                        'accepted_count' => $invitedToObject->participants()->whereIn('status', [
                            ParticipantAcceptanceState::ACCEPTED,
                            ParticipantAcceptanceState::ACCEPTED_NOT_READY,
                        ])->count(),
                    ];
                } else {
                    $gameTitle = $invitedToObject->game->title;
                    $teamDetails = [
                        'game' => $gameTitle,
                        'members' => $invitedToObject->players()->get()->toArray(),
                        'members_count' => $invitedToObject->players()->count(),
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
     * @param User $user
     * @param int $teamId
     * @return
     */
    public function acceptTeamInvitation(User $user, int $teamId)
    {
        $invitations = $this->getInvitations($user, $teamId, Team::class);
        if ($invitations) {
            $firstInvitation = $invitations->first();
            $team = Team::find($firstInvitation->invite_aware_id);
            if ($team->players()->where('user_id', $user->id)->count() == 0) {
                $team->players()->syncWithoutDetaching([$user->id], ['captain' => false]);
            }
            $this->removeInvitations($invitations->all());
            return $team->players()->where('user_id', $user->id)->get();
        }
        return [];
    }

    /**
     * @param User $user
     * @param int $teamId
     * @return
     */
    public function declineTeamInvitation(User $user, int $teamId)
    {
        $invitations = $this->getInvitations($user, $teamId, Team::class);
        if ($invitations) {
            $this->removeInvitations($invitations->all());
        }
        return [];
    }

    /**
     * @param User $user
     * @param int $participantableId
     * @param int $tournamentId
     * @return array
     */
    public function acceptTournamentInvitation(User $user, int $participantableId, int $tournamentId)
    {
        $invitations = $this->getInvitations($user, $tournamentId, Tournament::class);
        if ($invitations) {
            $firstInvitation = $invitations->first();
            $tournament = Tournament::find($firstInvitation->invite_aware_id);
            if ($tournament->players == 1) {
                if (
                    $user->id == $participantableId &&
                    $tournament->participants()
                        ->where('participantable_type', User::class)
                        ->where('participantable_id', $user->id)
                        ->count() == 0
                ) {
                    $participant = new Participant([
                        'participantable_type' => User::class,
                        'participantable_id' => $user->id,
                        'status' => ParticipantAcceptanceState::ACCEPTED
                    ]);
                    $tournament->participants()->save($participant);
                    $this->removeInvitations($invitations->all());
                    return $tournament->participants()
                        ->where('participantable_type', User::class)
                        ->where('participantable_id', $user->id)
                        ->get();
                }
            } else {
                $team = Team::find($participantableId);
                if ($team->players()->wherePivot('captain', 1)->first()->user_id == $user->id) {
                    if ($team->players()->count() >= $tournament->players) {
                        $status = ParticipantAcceptanceState::ACCEPTED;
                    } else {
                        $status = ParticipantAcceptanceState::ACCEPTED_NOT_READY;
                    }
                    $participant = new Participant([
                        'participantable_type' => Team::class,
                        'participantable_id' => $team->id,
                        'status' => $status
                    ]);
                    $tournament->participants()->save($participant);
                    event(new ParticipantStatusWasUpdated($participant));
                }
                $this->removeInvitations($invitations->all());
                return $tournament->participants()
                    ->where('participantable_type', Team::class)
                    ->where('participantable_id', $team->id)
                    ->get();
            }
        }
        return [];
    }

    /**
     * @param User $user
     * @param int $tournamentId
     * @return array
     */
    public function declineTournamentInvitation(User $user, int $tournamentId)
    {
        $invitations = $this->getInvitations($user, $tournamentId, Tournament::class);
        if ($invitations) {
            $this->removeInvitations($invitations->all());
        }
        return [];
    }

    /**
     * @param Invitation $invitation
     * @param User|null $invitee
     */
    private function fireCreationEvents(Invitation $invitation, ?User $invitee)
    {
        event(new InvitationCreated($invitation, $invitee));
    }

    /**
     * @param User $user
     * @param int $inviteAwareId
     * @param string $inviteeAwareType
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getInvitations(User $user, int $inviteAwareId, string $inviteeAwareType)
    {
        return Invitation::query()
            ->where('invite_aware_id', $inviteAwareId)
            ->where('invite_aware_type', $inviteeAwareType)
            ->where('email', $user->email)
            ->get();
    }

    /**
     * @param array $invitations
     */
    private function removeInvitations(array $invitations)
    {
        foreach ($invitations as $invitation) {
            $invitation->delete();
        }
    }

    /**
     * @param User $user
     * @param Tournament $tournament
     * @return array
     */
    private function participantablesForTournament(User $user, Tournament $tournament)
    {
        if ($tournament->players == 1) {
            $participantables = [
                'candidates' => [
                    [
                        'id' => $user->id,
                        'logo' => $user->avatar,
                        'title' => $user->username,
                        'members_count' => 1,
                    ]
                ],
                'minimum_members_count' => 1,
                'can_create_more' => false,
            ];
        } else {
            $participantables = [
                'can_create_more' => true,
                'minimum_members_count' => $tournament->players,
                'candidates' => [],
            ];
            $teams = $user->teams()
                ->where('game_id', $tournament->game->id)
                ->wherePivot('captain', 1)
                ->get()->all();
            foreach ($teams as $team) {
                $participantables['candidates'][] = [
                    'id' => $team->id,
                    'logo' => $team->logo,
                    'title' => $team->title,
                    'members_count' => $team->players()->count(),
                ];
            }
        }
        return $participantables;
    }
}
