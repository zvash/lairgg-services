<?php

namespace App\Repositories;


use App\Enums\DisputeState;
use App\Events\MatchLobbyHadAnAction;
use App\Play;
use App\User;
use App\Dispute;

class DisputeRepository extends BaseRepository
{

    protected $modelClass = Dispute::class;

    /**
     * Close given dispute
     *
     * @param Dispute $dispute
     * @return Dispute
     */
    public function close(Dispute $dispute)
    {
        $dispute->state = DisputeState::CLOSED;
        $dispute->save();
        event(new MatchLobbyHadAnAction($dispute->match, request()->user(), 'dispute_closed'));
        return $dispute;
    }
}
