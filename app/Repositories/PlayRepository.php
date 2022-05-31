<?php

namespace App\Repositories;


use App\Events\MatchScoreWasSubmitted;
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
     * @throws \Exception
     */
    public function setPlayScoreWithRequest(Request $request, Play $play)
    {
        $this->checkIfMatchScoresAreEditable($play->match);
        $play = $this->editPlayWithRequest($request, $play, $request->user());
        $scoreRecords = $request->get('scores');
        return $this->setPlayScores($play, $scoreRecords, $request->user());
    }

    /**
     * @param Match $match
     * @throws \Exception
     */
    private function checkIfMatchScoresAreEditable(Match $match)
    {
        if (!$match->partiesAreReady()) {
            throw new \Exception('Participants of the match are not determined yet.');
        }

        $nextMatchForWinner = $match->getNextMatchForWinner();
        if ($nextMatchForWinner) {
            if ($nextMatchForWinner->isOver() || $nextMatchForWinner->isActive()) {
                throw new \Exception('Next match has already started. Scores are not editable.');
            }
        }

        $nextMatchForLoser = $match->getNextMatchForLoser();
        if ($nextMatchForLoser) {
            if ($nextMatchForLoser->isOver() || $nextMatchForLoser->isActive()) {
                throw new \Exception('Next match has already started. Scores are not editable.');
            }
        }
    }

    /**
     * @param Play $play
     * @param array $scoreRecords
     * @param User $user
     * @return Play
     * @throws \Exception
     */
    public function setPlayScores(Play $play, array $scoreRecords, ?User $user): Play
    {
        $numberOfForfeiters = 0;
        foreach ($scoreRecords as $scoreRecord) {
            if ($scoreRecord['is_forfeit']) {
                $numberOfForfeiters++;
            }
        }
        if ($numberOfForfeiters > count($scoreRecords) - 1) {
            throw new \Exception('Too many participants has forfeited the game');
        }
        $notify = false;
        foreach ($scoreRecords as $record) {
            $party = $play->parties()->whereId($record['party_id'])->first();
            if ($party) {
                if ($record['is_forfeit']) {
                    $record['score'] = 0;
                    $record['is_winner'] = false;
                }

                if (! array_key_exists('score', $record) || empty($record['score'])) {
                    $record['score'] = 0;
                    if ($record['is_winner']) {
                        $record['score'] = 1;
                    }
                }

                $party->setAttribute('score', $record['score'])
                    ->setAttribute('is_winner', $record['is_winner'])
                    ->setAttribute('is_forfeit', $record['is_forfeit'])
                    ->save();
                $notify = true;
            }
        }
        if ($notify && $user) {
            event(new MatchScoreWasSubmitted($play->match, $user));
        }
        $match = $play->match;
        $winnerAndLosers = $match->getWinnerAndLosers();
        if ($match->isOver()) {
            if ($winnerAndLosers['winner_id']) {
                $match->setAttribute('winner_team_id', $winnerAndLosers['winner_id'])->save();
                $match->addWinnerToNextMatchForWinners();
                $match->addLoserToNextMatchForLosers();
            } else {
                $this->removeParticipantsFromNextMatches($match, $winnerAndLosers);
            }
        } else {
            $this->removeParticipantsFromNextMatches($match, $winnerAndLosers);
        }
        return $play;
    }

    /**
     * @param Match $match
     * @param array $winnerAndLosers
     */
    private function removeParticipantsFromNextMatches(Match $match, array $winnerAndLosers): void
    {
        $currentWinner = $match->winner_team_id;
        if ($currentWinner) {
            $match->setAttribute('winner_team_id', null)->save();
            $match->removeParticipantFromNextMatchForWinners($currentWinner);
            $loserIds = isset($winnerAndLosers['losers_ids']) ? $winnerAndLosers['losers_ids'] : [];
            foreach ($loserIds as $loserId) {
                $match->removeParticipantFromNextMatchForLosers($loserId);
            }
        }
    }
}
