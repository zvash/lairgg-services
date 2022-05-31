<?php

namespace App;

use App\Traits\Eloquents\LobbyAware;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

/**
 * @property int group
 * @property int round
 * @property Tournament tournament
 */
class Match extends Model
{
    use Actionable,
        LobbyAware;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'play_count' => 'integer',
        'round' => 'integer',
        'group' => 'integer',
        'is_forfeit' => 'boolean',
        'started_at' => 'datetime',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'play_count' => 1,
        'is_forfeit' => false,
    ];

    protected $appends = [
        'has_started',
        'has_finished',
    ];

    /**
     * Get the tournament that owns the match.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the winner that owns the match.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function winner()
    {
        return $this->belongsTo(Participant::class, 'winner_team_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function winnerNextMatch()
    {
        return $this->belongsTo(Match::class, 'winner_next_match_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function loserNextMatch()
    {
        return $this->belongsTo(Match::class, 'loser_next_match_id');
    }

    /**
     * Get the plays for the match.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function plays()
    {
        return $this->hasMany(Play::class);
    }

    /**
     * Get matches within the first round of the given group
     *
     * @param Builder $query
     * @param int $group
     * @return Builder
     */
    public function scopeFirstRoundOfGroup(Builder $query, int $group)
    {
        return $query->where('group', $group)->where('round', 1);
    }

    /**
     * Get matches within the first round of all available groups
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeFirstRoundOfAllGroups(Builder $query)
    {
        return $query->where('round', 1);
    }

    /**
     * @return bool
     */
    public function getHasStartedAttribute()
    {
        return $this->matchHasStarted();
    }

    /**
     * @return bool
     */
    public function getHasFinishedAttribute()
    {
        if ($this->winner_team_id) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function matchHasStarted()
    {
        if ($this->winner_team_id) {
            return true;
        }
        if (!$this->started_at) {
            return false;
        }
        return strtotime($this->started_at->format('Y-m-d H:i:s')) < time();
    }

    /**
     * @return Match[]|null
     */
    public function getPreviousMatches()
    {
        $tournament = $this->tournament;
        $engine = $tournament->engine();
        return $engine->getPreviousMatches($this);
    }

    /**
     * @param Participant $participant
     * @return Match|null
     */
    public function getPreviousMatchWithoutParticipant(Participant $participant)
    {
        $previousMatches = $this->getPreviousMatches();
        if ($previousMatches) {
           foreach ($previousMatches as $match) {
               $participantIds = $match->getParticipants()->pluck('id')->toArray();
               if (! in_array($participant->id, $participantIds)) {
                   return $match;
               }
           }
        }
        return null;
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getParticipants()
    {
        $firstPlay = $this->plays()->first();
        $participantIds = $firstPlay->parties->pluck('team_id');
        return Participant::query()
            ->whereIn('id', $participantIds)
            ->with('participantable')
            ->get();
    }

    /**
     * @return bool
     */
    public function isOver()
    {
        $scores = $this->getMidGameScores()->all();
        $playsCount = $this->plays()->count();
        $halfScore = intval($playsCount / 2);
        foreach ($scores as $record) {
            if ($record['score'] > $halfScore) {
                return true;
            }
        }
        return false;
//        return $this->plays()->whereHas('parties', function ($parties) {
//            return $parties->whereNull('score');
//        })->count() == 0;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return !$this->winner_team_id &&
            $this->plays()->whereHas('parties', function ($parties) {
                return $parties->whereNotNull('score');
            })->count() > 0;
    }

    /**
     * @return bool
     */
    public function isRestMatch()
    {
        $participants = $this->getParticipants();
        $participantsCount = $participants->count();
        if ($participantsCount != 1) {
            return false;
        }
        $participantId = $participants->first()->id;
        $previousMatches = $this->getPreviousMatches();
        if (!$previousMatches) {
            return true;
        }
        foreach ($previousMatches as $previousMatch) {
            $previousMatchParticipantsIds = $previousMatch->getParticipants()->pluck('id')->all();
            if (in_array($participantId, $previousMatchParticipantsIds)) {
                continue;
            }
            if (count($previousMatchParticipantsIds) == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getMidGameScores()
    {
        $playsIds = $this->plays->pluck('id')->all();
        $playsIds[] = 0;
        return Party::query()
            ->selectRaw('team_id as participant_id, SUM(is_winner) as score')
            ->whereNotNull('is_winner')
            ->whereIn('play_id', $playsIds)
            ->groupBy('team_id')
            ->orderBy('score')
            ->get();
    }

    public function getWinnerAndLosers()
    {
        $scores = $this->getMidGameScores();
        $maxScore = -1;
        $winner = null;
        $losers = [];
        foreach ($scores as $scoreRecord) {
            if ($scoreRecord['score'] > $maxScore) {
                if ($winner) {
                    $losers[] = $winner;
                }
                $winner = $scoreRecord['participant_id'];
                $maxScore = $scoreRecord['score'];
            } else if ($scoreRecord['score'] == $maxScore) {
                if ($winner) {
                    $losers[] = $winner;
                }
                $losers[] = $scoreRecord['participant_id'];
                $winner = null;
            } else {
                $losers[] = $scoreRecord['participant_id'];
            }
        }
        return [
            'winner_id' => $winner,
            'losers_ids' => $losers,
        ];
    }

    /**
     * @return Match|null
     */
    public function getNextMatchForWinner()
    {
        return $this->winnerNextMatch;
    }

    /**
     * @return Match|null
     */
    public function getNextMatchForLoser()
    {
        return $this->loserNextMatch;
    }

    /**
     *
     */
    public function addWinnerToNextMatchForWinners()
    {
        $nextMatch = $this->tournament->engine()->getNextMatchForWinner($this);
        $participantId = $this->winner_team_id;
        if ($nextMatch) {
            $this->addParticipantToNextMatch($nextMatch, $participantId);
        }
    }

    /**
     * @param int $participantId
     */
    public function removeParticipantFromNextMatchForWinners(int $participantId)
    {
        $nextMatch = $this->tournament->engine()->getNextMatchForWinner($this);
        if ($nextMatch) {
            $this->removeParticipantFromMatch($participantId, $nextMatch);
        }
    }

    /**
     * @param int $participantId
     */
    public function removeParticipantFromNextMatchForLosers(int $participantId)
    {
        $nextMatch = $this->tournament->engine()->getNextMatchForLoser($this);
        if ($nextMatch) {
            $this->removeParticipantFromMatch($participantId, $nextMatch);
        }
    }

    /**
     *
     */
    public function addLoserToNextMatchForLosers()
    {
        $nextMatch = $this->tournament->engine()->getNextMatchForLoser($this);
        $participants = $this->getParticipants();
        $participantId = null;
        foreach ($participants as $participant) {
            if ($participant->id != $this->winner_team_id) {
                $participantId = $participant->id;
                break;
            }
        }
        if ($nextMatch && $participantId) {
            $this->addParticipantToNextMatch($nextMatch, $participantId);
        }
    }

    /**
     * @return bool
     */
    public function partiesAreReady()
    {
        $firstPlay = $this->plays()->first();
        if ($firstPlay) {
            return $firstPlay->parties()->whereNotNull('team_id')->count() > 0;
        }
        return false;
    }

    private function addParticipantToNextMatch(Match $nextMatch, int $participantId)
    {
        $participantsIds = $this->getParticipants()->pluck('id')->all();
        $playsIds = $nextMatch->plays->pluck('id')->all();
        $playsIds[] = 0;
        $parties = Party::query()
            ->whereIn('play_id', $playsIds)
            ->whereIn('team_id', $participantsIds)
            ->get();
        if ($parties->count()) {
            foreach ($parties as $party) {
                $party->setAttribute('team_id', $participantId)->save();
            }
        } else {
            $plays = $nextMatch->plays;
            foreach ($plays as $play) {
                $firstEmptyParty = $play->parties()
                    ->whereNull('team_id')
                    ->first();
                if ($firstEmptyParty) {
                    $firstEmptyParty->setAttribute('team_id', $participantId)->save();
                }
            }
        }
    }

    /**
     * @param Participant $participant
     * @return int|null
     */
    public function getParticipantScore(Participant $participant)
    {
        if (! $this->winner) {
            return null;
        }
        return $this->plays()
            ->whereHas('parties', function ($parties) use ($participant) {
                return $parties->where('team_id', $participant->id)
                    ->where('is_winner', true);
            })->count();

    }

    /**
     * @param Participant $participant
     * @return int|null
     */
    public function getParticipantCurrentScore(Participant $participant)
    {
        return $this->plays()
            ->whereHas('parties', function ($parties) use ($participant) {
                return $parties->where('team_id', $participant->id)
                    ->where('is_winner', true);
            })->count();

    }

    public function getRoundTitle()
    {
        $engine = $this->tournament->engine();
        return $engine->getRoundTitle($this);
    }

    /**
     * @return array
     */
    public function getCandidates()
    {
        $candidates = [];
        $engine = $this->tournament->engine();
        $numberOfPlayers = $engine->matchPlayerCount();
        $participants = $this->getParticipants();
        $definiteParticipant = null;
        for ($i = 0; $i < $numberOfPlayers; $i++) {
            if (isset($participants[$i])) {
                $definiteParticipant = $participants[$i];
                $logo = $definiteParticipant->getAvatar();
                $title = $definiteParticipant->getName();
                $score = $this->getParticipantScore($definiteParticipant);
                $isWinner = $this->winner_team_id == $definiteParticipant->id;
                $candidates[] = [
                    'logo' => $logo,
                    'title' => $title,
                    'score' => $score,
                    'is_winner' => $isWinner,
                ];
            } else if ($this->group == 1 && $this->round == 1) {
                $candidates[] = [
                    'logo' => null,
                    'title' => null,
                    'score' => null,
                    'is_winner' => null,
                ];
            } else if ($definiteParticipant) {
                $previousMatch = $this->getPreviousMatchWithoutParticipant($definiteParticipant);
                $previousParticipants = $previousMatch->getParticipants();
                if ($previousParticipants->count() == $numberOfPlayers) {
                    $names = [];
                    foreach ($previousParticipants as $previousParticipant) {
                        $names[] = $previousParticipant->getName();
                    }
                    $candidates[] = [
                        'logo' => null,
                        'title' => implode(' vs. ', $names),
                        'score' => null,
                        'is_winner' => null,
                    ];
                } else {
                    $candidates[] = [
                        'logo' => null,
                        'title' => null,
                        'score' => null,
                        'is_winner' => null,
                    ];
                }
            } else {
                $previousMatches = $this->getPreviousMatches();
                if (! $previousMatches) {
                    $candidates[] = [
                        'logo' => null,
                        'title' => null,
                        'score' => null,
                        'is_winner' => null,
                    ];
                    continue;
                }
                foreach ($previousMatches as $previousMatch) {
                    $previousParticipants = $previousMatch->getParticipants();
                    if ($previousParticipants->count() == $numberOfPlayers) {
                        $names = [];
                        foreach ($previousParticipants as $previousParticipant) {
                            $names[] = $previousParticipant->getName();
                        }
                        $candidates[] = [
                            'logo' => null,
                            'title' => implode(' vs. ', $names),
                            'score' => null,
                            'is_winner' => null,
                        ];
                    } else {
                        $candidates[] = [
                            'logo' => null,
                            'title' => null,
                            'score' => null,
                            'is_winner' => null,
                        ];
                    }
                }
                break;
            }
        }
        return $candidates;
    }

    public function getWinnerRank()
    {
        $tournamentType = TournamentType::where('id', $this->tournament->tournament_type_id)->first();
        $matchPosition = $this->roundPositionInGroup();
        if ($tournamentType->title == 'Single Elimination') {
            if ($matchPosition['group'] == 1 && $matchPosition['round'] == $matchPosition['max_round']) {
                return 1;
            } else if ($matchPosition['group'] == 2) {
                return 3;
            }
            return null;
        }
        if ($tournamentType->title == 'Double Elimination') {
            if ($matchPosition['group'] == 3) {
                return 1;
            }
            return null;
        }
        return null;
    }

    public function getLoserRank()
    {
        $tournamentType = TournamentType::where('id', $this->tournament->tournament_type_id)->first();
        $matchPosition = $this->roundPositionInGroup();
        if ($tournamentType->title == 'Single Elimination') {
            if ($matchPosition['group'] == 1 && $matchPosition['round'] == $matchPosition['max_round']) {
                return 2;
            } else if ($matchPosition['group'] == 2) {
                return 4;
            }
            return null;
        }
        if ($tournamentType->title == 'Double Elimination') {
            if ($matchPosition['group'] == 3) {
                return 2;
            } else if ($matchPosition['group'] == 2) {
                return 3 + $matchPosition['max_round'] - $matchPosition['round'];
            }
            return null;
        }
        return null;
    }

    private function roundPositionInGroup()
    {
        $maxRoundInGroup = $this->tournament->matches()->where('group', $this->group)->max('round');
        $numberOfGroups = $this->tournament->matches()->max('group');
        return [
            'round' => $this->round,
            'max_round' => $maxRoundInGroup,
            'group' => $this->group,
            'number_of_groups' => $numberOfGroups,
        ];
    }

    /**
     * @param int $participantId
     * @param Match|null $match
     */
    private function removeParticipantFromMatch(int $participantId, ?Match $match): void
    {
        $playsIds = $match->plays->pluck('id')->all();
        $playsIds[] = 0;
        $parties = Party::query()
            ->whereIn('play_id', $playsIds)
            ->where('team_id', $participantId)
            ->get();
        foreach ($parties as $party) {
            $party->setAttribute('team_id', null)
                ->setAttribute('score', null)
                ->setAttribute('is_winner', 0)
                ->setAttribute('is_forfeit', 0)
                ->save();
        }
    }
}
