<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\HttpStatusCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelInvitationRequest;
use App\Http\Requests\DeleteTeamImagesRequest;
use App\Http\Requests\JoinTeamByUrlRequest;
use App\Http\Requests\PromoteToCaptainRequest;
use App\Http\Requests\RemoveFromTeamRequest;
use App\Http\Requests\ShareGemRequest;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Player;
use App\Repositories\InvitationRepository;
use App\Repositories\MatchRepository;
use App\Repositories\TeamRepository;
use App\Team;
use App\Traits\Responses\ResponseMaker;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TeamController extends Controller
{
    use ResponseMaker;

    /**
     * @param StoreTeamRequest $request
     * @param TeamRepository $teamRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(StoreTeamRequest $request, TeamRepository $teamRepository)
    {
        return $this->success($teamRepository->createTeamFromRequest($request));
    }

    /**
     * @param UpdateTeamRequest $request
     * @param Team $team
     * @param TeamRepository $teamRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(UpdateTeamRequest $request, Team $team, TeamRepository $teamRepository)
    {
        $gate = Gate::inspect('canUpdate', $team);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::FORBIDDEN);
        }
        return $this->success($teamRepository->updateTeam($request, $team));
    }

    /**
     * @param UpdateTeamRequest $request
     * @param Team $team
     * @param TeamRepository $teamRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function removeImages(DeleteTeamImagesRequest $request, Team $team, TeamRepository $teamRepository)
    {
        $gate = Gate::inspect('canUpdate', $team);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::FORBIDDEN);
        }
        return $this->success($teamRepository->removeTeamImages($request, $team));
    }

    /**
     * @param ShareGemRequest $request
     * @param Team $team
     * @param TeamRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function shareGems(ShareGemRequest $request, Team $team, TeamRepository $repository)
    {
        $gate = Gate::inspect('canShareGem', $team);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::FORBIDDEN);
        }
        $validated = $request->validated();
        $slices = $validated['slices'];
        $balanceId = $validated['balance_id'];
        try {
            return $this->success(['remained_gems' => $repository->shareGems($team, $balanceId, $slices)]);
        } catch (\Exception $exception) {
            return $this->failMessage($exception->getMessage(), 400);
        }
    }

    /**
     * @param Request $request
     * @param Team $team
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request, Team $team)
    {
        return $this->success($team->load(['players', 'links', 'links.linkType']));
    }

    /**
     * @param Request $request
     * @param Team $team
     * @param InvitationRepository $invitationRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function invite(Request $request, Team $team, InvitationRepository $invitationRepository)
    {
        $gate = Gate::inspect('canInviteParticipant', $team);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::FORBIDDEN);
        }

        list($identifier, $user) = $this->validateParticipantIdentifier($request);
        $invitationRepository->createTeamInvitation($team, $identifier, Auth::user(), $user);
        $message = __('strings.invitation.invited_to_team', [
            'identifier' => $identifier,
            'team_title' => $team->title
        ]);
        return $this->success(['message' => $message]);
    }

    /**
     * @param Team $team
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function players(Team $team)
    {
        return $this->success($team->players->makeHidden('pivot'));
    }

    public function specificTeamInfo(Request $request, Team $team, TeamRepository $repository)
    {
        return $this->success($repository->info($team));
    }

    /**
     * @param PromoteToCaptainRequest $request
     * @param Team $team
     * @param TeamRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function promoteToCaptain(PromoteToCaptainRequest $request, Team $team, TeamRepository $repository)
    {
        $validated = $request->validated();
        $gate = Gate::inspect('canPromoteToCaptain', $team);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::FORBIDDEN);
        }

        try {
            return $this->success(['new_captain_user_id' => $repository->promote($team, $validated['user_id'])]);
        } catch (\Exception $exception) {
            return $this->failMessage($exception->getMessage(), 400);
        }
    }

    /**
     * @param PromoteToCaptainRequest $request
     * @param Team $team
     * @param TeamRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function removeFromTeam(RemoveFromTeamRequest $request, Team $team, TeamRepository $repository)
    {
        $validated = $request->validated();
        $gate = Gate::inspect('canRemovePlayer', $team);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::FORBIDDEN);
        }

        if ($validated['user_id'] == $request->user()->id) {
            return $this->failMessage(__('strings.team.captain_cannot_be_removed'), 400);
        }

        try {
            return $this->success(['message' => $repository->removeFromTeam($team, $validated['user_id'])]);
        } catch (\Exception $exception) {
            return $this->failMessage($exception->getMessage(), 400);
        }
    }

    /**
     * @param Request $request
     * @param Team $team
     * @param TeamRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function leaveTeam(Request $request, Team $team, TeamRepository $repository)
    {
        $user = $request->user();
        try {
            return $this->success(['message' => $repository->leaveTeam($user, $team)]);
        } catch (\Exception $exception) {
            return $this->failMessage($exception->getMessage(), 400);
        }
    }

    /**
     * @param Request $request
     * @param Team $team
     * @param TeamRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function deleteTeam(Request $request, Team $team, TeamRepository $repository)
    {
        $gate = Gate::inspect('canDeleteTeam', $team);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::FORBIDDEN);
        }

        try {
            return $this->success(['message' => $repository->deleteTeam($team)]);
        } catch (\Exception $exception) {
            return $this->failMessage($exception->getMessage(), 400);
        }
    }

    /**
     * @param Request $request
     * @param Team $team
     * @param TeamRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function overview(Request $request, Team $team, TeamRepository $repository)
    {
        $viewer = $request->user();
        return $this->success($repository->overview($team, $viewer));
    }

    /**
     * @param Request $request
     * @param Team $team
     * @param TeamRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function tournaments(Request $request, Team $team, TeamRepository $repository)
    {
        return $this->success($repository->getTeamTournamentsAndMatches($team));
    }

    /**
     * @param Request $request
     * @param Team $team
     * @param TeamRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function awards(Request $request, Team $team, TeamRepository $repository)
    {
        return $this->success($repository->awardsOfTeam($team));
    }

    /**
     * @param Request $request
     * @param Team $team
     * @param TeamRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getJoinUrl(Request $request, Team $team, TeamRepository $repository)
    {
        $gate = Gate::inspect('canAccessJoinUrl', $team);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::FORBIDDEN);
        }
        return $this->success(['url' => $repository->getJoinUrl($team)]);
    }

    /**
     * @param Request $request
     * @param Team $team
     * @param TeamRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function setJoinUrl(Request $request, Team $team, TeamRepository $repository)
    {
        $gate = Gate::inspect('canSetJoinUrl', $team);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::FORBIDDEN);
        }
        return $this->success($repository->setJoinUrl($team));
    }

    /**
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function joinByUrlRequest(Request $request, string $token)
    {
        return $this->success(['message' => 'In order to join a team you should install lair.gg app']);
    }

    /**
     * @param JoinTeamByUrlRequest $request
     * @param InvitationRepository $invitationRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function joinByUrl(JoinTeamByUrlRequest $request, InvitationRepository $invitationRepository)
    {
        $validated = $request->validated();
        $user = $request->user();
        $identifier = $user->username;
        $team = Team::query()->where('join_url', $validated['token'])->first();
        if ($team) {
            $captain = Player::whereTeamId($team->id)
                ->whereCaptain(true)
                ->first()
                ->user;
            $invitationRepository->createTeamInvitation($team, $identifier, $captain, $user);
            $message = __('strings.invitation.invited_to_team', [
                'identifier' => $identifier,
                'team_title' => $team->title
            ]);
            return $this->success(['message' => $message]);
        }
        return $this->failMessage(__('strings.invitation.join_url_is_not_valid'), 402);

    }

    /**
     * @param Request $request
     * @param Team $team
     * @param InvitationRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function searchUser(Request $request, Team $team, InvitationRepository $repository)
    {
        $identifier = $request->get('identifier');
        return $this->success($repository->searchUsersForInvitation($team, $identifier));
    }

    /**
     * @param CancelInvitationRequest $request
     * @param Team $team
     * @param InvitationRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function cancelInvitation(CancelInvitationRequest $request, Team $team, InvitationRepository $repository)
    {
        $gate = Gate::inspect('canCancelInvitation', $team);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::FORBIDDEN);
        }
        $validated = $request->validated();
        $repository->cancelTeamInvitation(
            User::find($validated['user_id']),
            $team
        );
        return $this->success(['message' => __('strings.done')]);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function validateParticipantIdentifier(Request $request)
    {
        $request->validate(['identifier' => 'required|string|filled']);
        $identifier = $request->get('identifier');
        $atSignPosition = strpos($identifier, '@');
        if ($atSignPosition === false) {
            $request->validate(['identifier' => 'exists:users,username']);
            $user = User::where('username', $identifier)->first();
            return [$identifier, $user];
        }
        $request->validate(['identifier' => 'email:rfc,dns']);
        $user = User::where('email', $identifier)->first();
        return [$identifier, $user];
    }
}
