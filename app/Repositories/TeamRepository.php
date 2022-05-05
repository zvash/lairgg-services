<?php

namespace App\Repositories;


use App\Game;
use App\Http\Requests\DeleteTeamImagesRequest;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Invitation;
use App\Link;
use App\LinkType;
use App\Match;
use App\Participant;
use App\Player;
use App\Prize;
use App\Team;
use App\TeamBalance;
use App\Tournament;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TeamRepository extends BaseRepository
{
    public $modelClass = Team::class;

    /**
     * @param StoreTeamRequest $request
     * @return mixed
     */
    public function createTeamFromRequest(StoreTeamRequest $request)
    {
        $inputs = $request->validated();
        $inputs['logo'] = $this->saveImageFromRequest($request, 'logo', 'teams/logos');
        $inputs['cover'] = $this->saveImageFromRequest($request, 'cover', 'teams/covers');
        $team = Team::create($inputs);
        $team->players()->attach(Auth::user()->id, ['captain' => true]);
        return $team;
    }

    /**
     * @param UpdateTeamRequest $request
     * @param Team $team
     * @return Team
     */
    public function updateTeam(UpdateTeamRequest $request, Team $team)
    {
        $inputs = $request->validated();
        if ($request->hasFile('logo')) {
            $inputs['logo'] = $this->saveImageFromRequest($request, 'logo', 'teams/logos');
        }
        if ($request->hasFile('cover')) {
            $inputs['cover'] = $this->saveImageFromRequest($request, 'cover', 'teams/covers');
        }
        $links = [];
        if (array_key_exists('links', $inputs)) {
            $links = $inputs['links'];
            unset($inputs['links']);
        }
        foreach ($inputs as $key => $value) {
            $team->setAttribute($key, $value);
        }
        $team->save();
        $team->links()->delete();
        if ($links) {
            $this->saveTeamLinks($team, $links);
        }
        return $team->load(['links', 'links.linkType']);
    }

    /**
     * @param DeleteTeamImagesRequest $request
     * @param Team $team
     * @return Team
     */
    public function removeTeamImages(DeleteTeamImagesRequest $request, Team $team)
    {
        $validated = $request->validated();
        if (!empty($validated['logo'])) {
            Storage::delete($team->logo);
            $team->logo = null;
        }
        if (!empty($validated['cover'])) {
            Storage::delete($team->cover);
            $team->cover = null;
        }
        $team->save();
        return $team;
    }

    public function info(Team $team)
    {
        return $team->load(['game']);
    }

    /**
     * @param Team $team
     * @param User $viewer
     * @return array
     */
    public function overview(Team $team, User $viewer)
    {
        $participantsIdsByTournamentIds = Participant::query()
            ->where('participantable_type', \App\Team::class)
            ->where('participantable_id', $team->id)
            ->get()
            ->pluck('id', 'tournament_id')
            ->all();
        $numberOfTournaments = count($participantsIdsByTournamentIds);

        $tournamentIds = array_keys($participantsIdsByTournamentIds);
        $participantIds = array_values($participantsIdsByTournamentIds);

        $matchesIdsByTournamentIds = \App\Match::selectRaw('MAX(id) as id, tournament_id')
            ->whereIn('tournament_id', $tournamentIds)
            ->whereHas('plays', function ($plays) use ($participantIds) {
                return $plays->whereHas('parties', function ($parties) use ($participantIds) {
                    return $parties->whereIn('team_id', $participantIds);
                });
            })->groupByRaw('tournament_id')->get()
            ->pluck('id', 'tournament_id')->all();
        $ranksCount = [
            1 => 0,
            2 => 0,
            3 => 0,
        ];
        $gemCount = 0;
        $pointTypeId = \App\ValueType::whereTitle('Point')->first()->id;
        $teamRankByTournament = [];
        foreach ($matchesIdsByTournamentIds as $matchId) {
            $match = Match::find($matchId);
            if ($match && $match->winner_team_id) {
                if ($match->winner_team_id == $participantsIdsByTournamentIds[$match->tournament_id]) {
                    $rank = $match->getWinnerRank();
                } else {
                    $rank = $match->getLoserRank();
                }
                if ($rank && in_array($rank, [1, 2, 3])) {
                    $ranksCount[$rank] += 1;
                    $gemCount += Prize::where('tournament_id', $match->tournament_id)
                        ->where('value_type_id', $pointTypeId)
                        ->where('rank', $rank)
                        ->sum('value');
                }
                if ($rank) {
                    $teamRankByTournament[$match->tournament_id] = $rank;
                }
            }
        }
        $balances = $team->balances()->whereTeamId($team->id)->where('points', '>', 0)->get();
        $availableGems = [];
        foreach ($balances as $balance) {
            if (isset($teamRankByTournament[$balance->tournament_id])) {
                $availableGems[] = [
                    'balance_id' => $balance->id,
                    'tournament_id' => $balance->tournament_id,
                    'team_id' => $team->id,
                    'tournament_title' => $balance->tournament->title,
                    'gems' => $balance->points,
                    'rank' => $teamRankByTournament[$balance->tournament_id],
                ];
            }
        }

        $result = $team->load(['players', 'links'])->toArray();
        $result += [
            'number_of_tournaments' => $numberOfTournaments,
            'ranks' => $ranksCount,
            'gems' => $gemCount,
            'viewer_is_captain' => $viewer->id == $team->players()->whereCaptain(1)->first()->user_id,
            'available_gems' => $availableGems,
        ];
        return $result;
    }

    /**
     * @param Team $team
     * @return array
     */
    public function getTeamTournamentsAndMatches(Team $team)
    {
        $participantsIdsByTournamentIds = Participant::query()
            ->where('participantable_type', Team::class)
            ->where('participantable_id', $team->id)
            ->pluck('id', 'tournament_id')
            ->all();
        $participantsIds = array_values($participantsIdsByTournamentIds);
        $participantsIds[] = 0;
        $tournamentIds = array_keys($participantsIdsByTournamentIds);
        $tournamentIds[] = 0;
        $result = [];
        $tournaments = Tournament::query()
            ->whereIn('id', $tournamentIds)
            ->with(['game', 'organization'])
            ->orderBy('id', 'desc')->get();
        foreach ($tournaments as $tournament) {
            $engine = $tournament->engine();
            $item = $tournament->toArray();
            $matches = $tournament->matches()->whereHas('plays', function ($plays) use ($participantsIds) {
                return $plays->whereHas('parties', function ($parties) use ($participantsIds) {
                    return $parties->whereIn('team_id', $participantsIds);
                });
            })->orderBy('id', 'desc')->get();
            $item['matches'] = [];
            $needRanking = true;
            $rank = null;
            foreach ($matches as $match) {
                if ($needRanking) {
                    if ($match->winner_team_id == $participantsIdsByTournamentIds[$match->tournament_id]) {
                        $rank = $match->getWinnerRank();
                    } else {
                        $rank = $match->getLoserRank();
                    }
                    $needRanking = false;
                }
                $candidates = $match->getCandidates();
                $matchArray = $match->toArray();
                $matchArray['round_title'] = $engine->getRoundTitle($match);
                unset($matchArray['tournament']);
                $matchArray['candidates'] = $candidates;
                $item['matches'][] = $matchArray;
                $item['rank'] = $rank;
            }
            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param Team $team
     * @return array
     */
    public function awardsOfTeam(Team $team)
    {
        $participantsIdsByTournamentIds = Participant::query()
            ->where('participantable_type', Team::class)
            ->where('participantable_id', $team->id)
            ->pluck('id', 'tournament_id')
            ->all();
        $participantsIds = array_values($participantsIdsByTournamentIds);
        $participantsIds[] = 0;
        $tournamentIds = array_keys($participantsIdsByTournamentIds);
        $tournamentIds[] = 0;
        $matchesIdsByTournamentIds = \App\Match::selectRaw('MAX(id) as id, tournament_id')
            ->whereIn('tournament_id', $tournamentIds)
            ->whereHas('plays', function ($plays) use ($participantsIds) {
                return $plays->whereHas('parties', function ($parties) use ($participantsIds) {
                    return $parties->whereIn('team_id', $participantsIds);
                });
            })->groupByRaw('tournament_id')->orderBy('id', 'desc')->get()
            ->pluck('id', 'tournament_id')->all();
        $prizes = [];
        foreach ($matchesIdsByTournamentIds as $matchId) {
            $rank = null;
            $match = Match::find($matchId);
            if ($match && $match->winner_team_id) {
                if ($match->winner_team_id == $participantsIdsByTournamentIds[$match->tournament_id]) {
                    $rank = $match->getWinnerRank();
                } else {
                    $rank = $match->getLoserRank();
                }
                if ($rank) {
                    $item = Tournament::where('id', $match->tournament_id)
                        ->with(['game', 'organization'])->first()->toArray();
                    $item['rank'] = $rank;
                    $item['prizes'] = Prize::query()->where('tournament_id', $match->tournament_id)
                        ->where('rank', $rank)
                        ->get()->toArray();
                    $prizes[] = $item;

                }
            }
        }
        return $prizes;
    }

    /**
     * @param Team $team
     * @param int $userId
     * @return int
     * @throws \Exception
     */
    public function promote(Team $team, int $userId)
    {
        if (Player::whereTeamId($team->id)->whereUserId($userId)->count() == 0) {
            throw new \Exception('New captain must be a member of the team');
        }
        DB::beginTransaction();
        try {
            Player::query()
                ->where('team_id', $team->id)
                ->update(['captain' => false]);
            Player::query()
                ->where('team_id', $team->id)
                ->where('user_id', $userId)
                ->update(['captain' => true]);
            DB::commit();
            return $userId;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception('Captain was not changed');
        }
    }

    /**
     * @param Team $team
     * @param int $userId
     * @return string
     * @throws \Exception
     */
    public function removeFromTeam(Team $team, int $userId)
    {
        if (Player::whereTeamId($team->id)->whereUserId($userId)->count() == 0) {
            throw new \Exception('Specified player is not a member of this team.');
        }
        Player::query()
            ->where('team_id', $team->id)
            ->where('user_id', $userId)
            ->delete();
        return 'done';
    }

    /**
     * @param User $user
     * @param Team $team
     * @return string
     * @throws \Exception
     */
    public function leaveTeam(User $user, Team $team)
    {
        $player = Player::whereTeamId($team->id)
            ->whereUserId($user->id)
            ->whereCaptain(true)
            ->first();
        if ($player) {
            $notCaptainPlayer = Player::whereTeamId($team->id)
                ->whereCaptain(false)
                ->first();
            if ($notCaptainPlayer) {
                $this->promote($team, $notCaptainPlayer->user_id);
            }
        }
        return $this->removeFromTeam($team, $user->id);
    }

    /**
     * @param Team $team
     * @return string
     * @throws \Exception
     */
    public function deleteTeam(Team $team)
    {
        DB::beginTransaction();
        try {
            $players = Player::whereTeamId($team->id)->get();
            foreach ($players as $player) {
                $this->removeFromTeam($team, $player->user_id);
            }
            Invitation::query()->where('invite_aware_type', 'App\Team')
                ->where('invite_aware_id', $team->id)->delete();
            $team->delete();
            DB::commit();
            return 'done';
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception('Team was not removed');
        }
    }

    /**
     * @param Team $team
     * @param int $balanceId
     * @param array $slices
     * @return mixed
     * @throws \Exception
     */
    public function shareGems(Team $team, int $balanceId, array $slices)
    {
        $playerUserIds = $team->players->pluck('user_id')->all();
        $sumOfGems = 0;
        foreach ($slices as $slice) {
            if (! in_array($slice['user_id'], $playerUserIds)) {
                throw new \Exception('User is not a member of the team.');
            }
            $sumOfGems += $slice['gems'];
        }
        $currentGems = 0;
        $balance = TeamBalance::whereId($balanceId)->whereTeamId($team->id)->first();
        if ($balance) {
            $currentGems = $balance->points;
        }
        if ($sumOfGems > $currentGems) {
            throw new \Exception('Insufficient gems.');
        }
        try {
            DB::beginTransaction();
            $balance->refresh();
            $balance->setAttribute('points', $currentGems - $sumOfGems)->save();
            foreach ($slices as $slice) {
                User::find($slice['user_id'])->points($slice['gems']);
            }
            DB::commit();
            return $team->balance->gems;
        } catch (\Exception $exception) {
            DB::rollBack();
        }
        return TeamBalance::find($balanceId)->points;
    }

    /**
     * @param Team $team
     * @return Team
     */
    public function setJoinUrl(Team $team)
    {
        $token = make_random_hash();
        if (Team::whereJoinUrl($token)->count()) {
            return $this->setJoinUrl($team);
        }
        $team->setAttribute('join_url', $token)->save();
        return $team;
    }

    /**
     * @param Team $team
     * @return Team
     */
    public function deleteJoinUrl(Team $team)
    {
        $team->setAttribute('join_url', null)->save();
        return $team;
    }

    /**
     * @param Team $team
     * @return string
     */
    public function getJoinUrl(Team $team)
    {
        if (! $team->join_url) {
            $team = $this->setJoinUrl($team);
        }
        return $team->join_full_url;
    }

    /**
     * @param Team $team
     * @param array $links
     */
    private function saveTeamLinks(Team $team, array $links)
    {
        $linkTypes = LinkType::query()
            ->whereNotIn('title', ['Email', 'Website'])
            ->get()
            ->all();
        $emailType = LinkType::query()
            ->where('title', 'Email')
            ->first();
        $websiteType = LinkType::query()
            ->where('title', 'Website')
            ->first();
        foreach ($links as $value) {
            $link = strtolower($value);
            if (filter_var($link, FILTER_VALIDATE_EMAIL)) {
                if ($emailType) {
                    $model = new Link([
                        'url' => $link,
                        'linkable_type' => Team::class,
                        'linkable_id' => $team->id,
                        'link_type_id' => $emailType->id,
                    ]);
                    $model->save();
                }
                continue;
            } else if (filter_var($link, FILTER_VALIDATE_DOMAIN)) {
                $registered = false;
                foreach ($linkTypes as $linkType) {
                    $address = strtolower($linkType->title) . '.com';
                    if (strpos($link, $address) !== false) {
                        $model = new Link([
                            'url' => $link,
                            'linkable_type' => Team::class,
                            'linkable_id' => $team->id,
                            'link_type_id' => $linkType->id,
                        ]);
                        $model->save();
                        $registered = true;
                        break;
                    }
                }
                if (!$registered && $websiteType) {
                    $model = new Link([
                        'url' => $link,
                        'linkable_type' => Team::class,
                        'linkable_id' => $team->id,
                        'link_type_id' => $websiteType->id,
                    ]);
                    $model->save();
                }
            }

        }
    }
}
