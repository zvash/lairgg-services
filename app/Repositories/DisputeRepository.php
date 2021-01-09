<?php

namespace App\Repositories;


use App\Play;
use App\User;
use App\Dispute;

class DisputeRepository extends BaseRepository
{

    protected $modelClass = Dispute::class;

    public function createDisputeForPlay(User $user, int $playId, string $text, ?string $screenshotPath = null)
    {

    }

    protected function issuerCanIssueDispute(Play $play, User $user)
    {

    }
}