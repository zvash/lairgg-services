<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
        return $this->success(['message' => 'done']);
    }
}
