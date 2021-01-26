<?php

namespace App\Repositories;


use App\Dispute;
use App\Match;
use App\Organization;
use App\Party;
use App\Play;
use App\Tournament;
use App\TournamentType;
use Illuminate\Http\Request;

class MatchRepository extends BaseRepository
{
    protected $modelClass = Match::class;

    /**
     * Reset Number of plays in a match
     *
     * @param Match $match
     * @param int $count
     * @return Match
     */
    public function resetPlayCountForMatch(Match $match, int $count)
    {
        $currentCount = $match->plays()->count();
        if ($currentCount == $count) {
            return $match;
        }
        $match->play_count = $count;
        $match->save();
        $parties = null;
        $firstPlay = $match
            ->plays()
            ->first();
        if ($firstPlay) {
            $parties = $firstPlay
                ->parties
                ->pluck('team_id')
                ->all();
        }
        if ($currentCount > $count) {
            $plays = $match->plays()->orderBy('updated_at')->get()->all();
            $toDelete = $currentCount - $count;
            for ($i = 0; $i < $toDelete; $i++) {
                $plays[$i]->delete();
            }
            return $match;
        }
        $tournament = $match->tournament;
        $partiesCount = $this->getTournamentPartiesCount($tournament);
        $toCreate = $count - $currentCount;
        $playIndexes = range(1, $toCreate);
        foreach ($playIndexes as $index) {
            $play = Play::create([
                'match_id' => $match->id
            ]);
            for ($i = 0; $i < $partiesCount; $i++) {
                $teamId = null;
                if ($parties && isset($parties[$i])) {
                    $teamId = $parties[$i];
                }
                Party::create([
                    'play_id' => $play->id,
                    'team_id' => $teamId
                ]);
            }
        }
        return $match;
    }

    /**
     * @param Match $match
     * @return mixed
     */
    public function getDisputes(Match $match)
    {
        $playIds = $match
            ->plays()
            ->pluck('id')
            ->all();
        $playIds[] = 0;

        $disputes = Dispute::whereIn('play_id', $playIds)->get();
        return $disputes;
    }

    /**
     * @param Tournament $tournament
     * @return int
     */
    private function getTournamentPartiesCount(Tournament $tournament)
    {
        $tournamentType = TournamentType::where('id', $tournament->tournament_type_id)->first();
        if ($tournamentType) {
            if (in_array($tournament->title, ['Single Elimination', 'Double Elimination', 'League'])) {
                return 2;
            }
            if (in_array($tournamentType->title, ['Round Robin', 'Battle Royale'])) {
                return $tournament->participants()->count();
            }
        }
        return 2;
    }
}