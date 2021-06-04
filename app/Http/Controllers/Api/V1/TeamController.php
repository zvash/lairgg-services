<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\HttpStatusCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeamRequest;
use App\Repositories\InvitationRepository;
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
     * @param Request $request
     * @param Team $team
     * @param InvitationRepository $invitationRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function invite(Request $request, Team $team, InvitationRepository $invitationRepository)
    {
        $gate = Gate::inspect('canInviteParticipant', $team);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::UNAUTHORIZED);
        }

        list($identifier, $user) = $this->validateParticipantIdentifier($request);
        $invitationRepository->createTeamInvitation($team, $identifier, Auth::user(), $user);
        return $this->success(['message' => "{$identifier} is invited to join the {$team->title} team."]);
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
