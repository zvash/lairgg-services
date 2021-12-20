<?php

namespace App\Repositories;


use App\Match;
use App\Play;
use App\User;
use Illuminate\Http\Request;


class PlayRepository extends BaseRepository
{
    protected $modelClass = Play::class;

    /**
     * Updates an existing Play
     *
     * @param Request $request
     * @param Play $play
     * @param User $user
     * @return Play
     */
    public function editPlayWithRequest(Request $request, Play $play, User $user)
    {
        $inputs = array_filter($request->all(), function ($key) {
            return in_array($key, ['map_id']);
        }, ARRAY_FILTER_USE_KEY);
        $screenshot = $this->saveImageFromRequest($request, 'screenshot', 'plays/screenshots');
        if ($screenshot) {
            $inputs['screenshot'] = $screenshot;
        }
        $inputs['edited_by'] = $user->id;
        foreach ($inputs as $key => $value) {
            $play->setAttribute($key, $value);
        }
        $play->save();
        return $play;
    }

    /**
     * @param Request $request
     * @param Play $play
     * @return Play
     */
    public function setPlayScoreWithRequest(Request $request, Play $play)
    {
        $play = $this->editPlayWithRequest($request, $play, $request->user());
        $scoreRecords = $request->get('scores');
        foreach ($scoreRecords as $record) {
            $party = $play->parties()->whereId($record['party_id'])->first();
            if ($party) {
                $party->setAttribute('score', $record['score'])
                    ->setAttribute('is_winner', $record['is_winner'])
                    ->save();
            }
        }
        $match = $play->match;
        if ($match->isOver()) {
            $winnerAndLosers = $match->getWinnerAndLosers();
            if ($winnerAndLosers['winner_id']) {
                $match->setAttribute('winner_team_id', $winnerAndLosers['winner_id'])->save();
                $match->addWinnerToNextMatchForWinners();
                $match->addLoserToNextMatchForLosers();
            }
        }
        return $play;
    }
}
