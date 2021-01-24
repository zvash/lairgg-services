<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\HttpStatusCode;
use App\Http\Controllers\Controller;
use App\Participant;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ParticipantController extends Controller
{
    use ResponseMaker;

    /**
     * Manually check in a participant of a tournament
     *
     * @param Request $request
     * @param Participant $participant
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function checkParticipantIn(Request $request, Participant $participant)
    {
        if (!$participant) {
            return $this->failNotFound();
        }
        $gate = Gate::inspect('checkIn', $participant);
        if ($gate->denied()) {
            return $this->failMessage($gate->message(), HttpStatusCode::UNAUTHORIZED);
        }
        if (!$participant->checked_in_at) {
            $participant->setAttribute('checked_in_at', date('Y-m-d H:i:s'))
                ->save();
        }
        return $this->success($participant);
    }

    /**
     * Get all players in a team
     *
     * @param Request $request
     * @param Participant $participant
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function players(Request $request, Participant $participant)
    {
        if (!$participant) {
            return $this->failNotFound();
        }

        $players = $participant
            ->participantable
            ->players
            ->makeHidden('pivot');
        return $this->success($players);
    }
}
