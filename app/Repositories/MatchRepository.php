<?php

namespace App\Repositories;


use App\Dispute;
use App\Map;
use App\Match;
use App\User;
use App\Organization;
use App\Participant;
use App\Party;
use App\Play;
use App\Team;
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

    public function specificMatchOverview(Match $match, User $user)
    {
        $userIsCaptain = false;
        $tournament = $match->tournament;
        $organizer = $tournament->organization;
        $game = $tournament->game;
        $matchParticipants = $match->getParticipants();
        $plays = $match->plays()->orderBy('id')->with(['map', 'parties'])->get();
        $information = [
            'id' => $match->id,
            'match_date' => $match->started_at,
            'games_per_match' => $match->play_count,
            'stage' => $match->getRoundTitle(),
            'winner_participant_id' => $match->winner_team_id,
            'organizer' => [
                'id' => $organizer->id,
                'logo' => $organizer->logo,
                'cover' => $organizer->cover,
            ],
            'tournament' => [
                'id' => $tournament->id,
                'title' => $tournament->title,
                'image' => $tournament->image,
                'logo' => $tournament->logo,
                'players' => $tournament->players,
            ],
            'game' => [
                'id' => $game->id,
                'title' => $game->title,
                'image' => $game->image,
                'cover' => $game->cover,
                'logo' => $game->logo,
            ],
            'maps' => Map::all()->toArray(),
        ];
        $participants = [];
        foreach ($matchParticipants as $participant) {
            $type = $participant->participantable_type == Team::class ? 'team' : 'user';
            $record = [
                'id' => $participant->id,
                'participantable_type' => $type,
                'score' => $match->getParticipantScore($participant),
                'is_winner' => $match->winner_team_id == $participant->id,
            ];
            if ($type == 'team') {
                $record['title'] = $participant->participantable->title;
                $record['image'] = $participant->participantable->logo;
                $participantPlayers = $participant->participantable->players;
                $players = [];
                foreach ($participantPlayers as $player) {
                    $playerUser = User::find($player->user_id);
                    $isCaptain = $player->captain == 1;
                    if ($isCaptain && $user->id == $playerUser->id) {
                        $userIsCaptain = true;
                    }
                    $players[] = [
                        'user_id' => $playerUser->id,
                        'username' => $playerUser->username,
                        'avatar' => $playerUser->avatar,
                        'is_captain' => $isCaptain,
                    ];
                }
                $record['players'] = $players;
            } else if ($type == 'user') {
                $record['title'] = $participant->participantable->username;
                $record['image'] = $participant->participantable->avatar;
                $players = [];
                $playerUser = User::find($participant->participantable_id);
                $isCaptain = true;
                if ($participant->participantable_id == $user->id) {
                    $userIsCaptain = true;
                }
                $players[] = [
                    'user_id' => $playerUser->id,
                    'username' => $playerUser->username,
                    'avatar' => $playerUser->avatar,
                    'is_captain' => $isCaptain,
                ];
                $record['players'] = $players;
            }
            $participants[] = $record;
        }
        $information['participants'] = $participants;
        $information['viewer_is_captain'] = $userIsCaptain;
        $information['plays'] = $plays->toArray();
        return $information;
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
