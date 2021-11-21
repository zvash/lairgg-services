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

    public function getRoundTitle()
    {

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
}
