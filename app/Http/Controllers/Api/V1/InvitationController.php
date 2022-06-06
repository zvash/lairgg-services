<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeclineTournamentInvitationRequest;
use App\Http\Requests\JoinTeamRequest;
use App\Http\Requests\JoinTournamentRequest;
use App\Repositories\InvitationRepository;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    use ResponseMaker;

    /**
     * @param Request $request
     * @param InvitationRepository $invitationRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function count(Request $request, InvitationRepository $invitationRepository)
    {
        $user = $request->user();
        $count = $invitationRepository->countInvitations($user);
        return $this->success(['invitations_count' => $count]);
    }

    /**
     * @param Request $request
     * @param string $type
     * @param InvitationRepository $invitationRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getInvitations(Request $request, string $type, InvitationRepository $invitationRepository)
    {
        $user = $request->user();
        return $this->success($invitationRepository->unansweredInvitations($user, $type));
    }

    /**
     * @param Request $request
     * @param InvitationRepository $invitationRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function flash(Request $request, InvitationRepository $invitationRepository)
    {
        $user = $request->user();
        $invitations = $invitationRepository->flashInvitations($user);
        return $this->success($invitations);
    }

    /**
     * @param Request $request
     * @param InvitationRepository $invitationRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function flashed(Request $request, InvitationRepository $invitationRepository)
    {
        $request->validate(['token' => 'required|string|filled']);
        $user = $request->user();
        $invitationRepository->flashedOnce($user, $request->get('token'));
        return $this->success(['message' => __('strings.done')]);
    }

    /**
     * @param JoinTeamRequest $request
     * @param InvitationRepository $invitationRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function joinTeam(JoinTeamRequest $request, InvitationRepository $invitationRepository)
    {
        $user = $request->user();
        $validated = $request->validated();
        return $this->success($invitationRepository->acceptTeamInvitation($user, $validated['invitation_id']));
    }

    /**
     * @param JoinTeamRequest $request
     * @param InvitationRepository $invitationRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function declineTeamInvitation(JoinTeamRequest $request, InvitationRepository $invitationRepository)
    {
        $user = $request->user();
        $validated = $request->validated();
        return $this->success($invitationRepository->declineTeamInvitation($user, $validated['invitation_id']));
    }

    /**
     * @param JoinTournamentRequest $request
     * @param InvitationRepository $invitationRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function joinTournament(JoinTournamentRequest $request, InvitationRepository $invitationRepository)
    {
        $user = $request->user();
        $validated = $request->validated();
        return $this->success($invitationRepository->acceptTournamentInvitation($user, $validated['participantable_id'], $validated['invitation_id']));
    }

    /**
     * @param DeclineTournamentInvitationRequest $request
     * @param InvitationRepository $invitationRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function declineTournamentInvitation(DeclineTournamentInvitationRequest $request, InvitationRepository $invitationRepository)
    {
        $user = $request->user();
        $validated = $request->validated();
        return $this->success($invitationRepository->declineTournamentInvitation($user, $validated['invitation_id']));
    }


}
