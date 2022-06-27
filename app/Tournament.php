<?php

namespace App;

use App\Engines\BattleRoyaleEngine;
use App\Engines\DoubleEliminationEngine;
use App\Engines\SingleEliminationEngine;
use App\Enums\ParticipantAcceptanceState;
use App\Enums\Status;
use App\Traits\Eloquents\{
    Bannerable, InviteAware, Joinable, Linkable, LobbyAware
};
use Illuminate\Database\Eloquent\{
    Builder, Model, SoftDeletes
};
use Laravel\Nova\Actions\Actionable;

/**
 * @property mixed matches
 */
class Tournament extends Model
{
    use Actionable,
        SoftDeletes,
        Linkable,
        Joinable,
        InviteAware,
        Bannerable,
        LobbyAware;

    protected $fillable = [
        'title',
        'description',
        'rules',
        'image',
        'cover',
        'timezone',
        'max_teams',
        'reserve_teams',
        'players',
        'check_in_period',
        'entry_fee',
        'listed',
        'join_request',
        'join_url',
        'status',
        'structure',
        'match_check_in_period',
        'match_play_count',
        'match_randomize_map',
        'match_third_rank',
        'league_win_score',
        'league_tie_score',
        'league_lose_score',
        'league_match_up_count',
        'region_id',
        'tournament_type_id',
        'game_id',
        'organization_id',
        'requires_score',
    ];

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
        'players' => 'integer',
        'max_teams' => 'integer',
        'reserve_teams' => 'integer',
        'check_in_period' => 'integer',
        'listed' => 'boolean',
        'join_request' => 'boolean',
        'entry_fee' => 'float',
        'status' => 'integer',
        'bracket_released_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'match_check_in_period' => 'integer',
        'match_play_count' => 'integer',
        'match_randomize_map' => 'boolean',
        'match_third_rank' => 'boolean',
        'league_win_score' => 'integer',
        'league_tie_score' => 'integer',
        'league_lose_score' => 'integer',
        'league_match_up_count' => 'integer',
        'featured' => 'boolean',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'timezone' => 'UTC',
        'listed' => true,
        'join_request' => true,
        'status' => Status::ACTIVE,
        'check_in_period' => 10,
        'match_check_in_period' => 10,
        'match_play_count' => 1,
        'match_randomize_map' => true,
        'match_third_rank' => false,
    ];

    protected $appends = [
        'check_in_is_allowed',
        'region_title',
        'accepted_count',
        'tournament_game',
        'organization_logo',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function announcements()
    {
        return $this->hasMany(TournamentAnnouncement::class);
    }

    /**
     * Get the region that owns the tournament.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the tournament type that owns the tournament.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tournamentType()
    {
        return $this->belongsTo(TournamentType::class);
    }

    /**
     * Get the organization that owns the tournament.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the game that owns the tournament.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the prizes for the tournament.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prizes()
    {
        return $this->hasMany(Prize::class);
    }

    /**
     * Get the participants for the tournament.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    /**
     * Get the matches for the tournament.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function matches()
    {
        return $this->hasMany(Match::class);
    }

    /**
     * get final match of the tournament
     *
     * @return Match|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getFinalMatch()
    {
        return $this->matches()->orderBy('id', 'desc')->first();
    }

    /**
     * @return bool
     */
    public function hasFinished()
    {
        return !!$this->getFinalMatch()->winner_team_id;

    }

    /**
     * @return array
     */
    public function getGemPrizesByRank()
    {
        $gemType = ValueType::query()
            ->where('title', 'Point')
            ->first();
        $prizes = $this->prizes()
            ->where('value_type_id', $gemType->id)
            ->get()
            ->all();
        if (! $prizes) {
            return [];
        }

        $sumOfGemPrizeByRank = [];
        foreach ($prizes as $prize) {
            if (!array_key_exists($prize['rank'], $sumOfGemPrizeByRank)) {
                $sumOfGemPrizeByRank[$prize['rank']] = 0;
            }
            $sumOfGemPrizeByRank[$prize['rank']] += $prize['value'];
        }
        return $sumOfGemPrizeByRank;
    }

    /**
     * @return Match[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getRankedMatches()
    {
        $finalMatch = $this->getFinalMatch();
        $matches = [$finalMatch->id];
        $this->addToRankedMatches($finalMatch, $matches);
        return Match::query()
            ->whereIn('id', $matches)
            ->get();
    }

    /**
     * @return array
     */
    public function getRankedParticipants()
    {
        $matches = $this->getRankedMatches();
        $participantsByRank = [];
        foreach ($matches as $match) {
            $winnerAndLosers = $match->getWinnerAndLosers();
            $winnerRank = $match->getWinnerRank();
            if ($winnerRank && $winnerAndLosers && !empty($winnerAndLosers['winner_id'])) {
                $participantsByRank[$winnerRank] = Participant::find($winnerAndLosers['winner_id']);
            }
            $loserRank = $match->getLoserRank();
            if ($loserRank && $winnerAndLosers && !empty($winnerAndLosers['losers_ids'])) {
                $participantsByRank[$loserRank] = Participant::find($winnerAndLosers['losers_ids'][0]);
            }
        }
        ksort($participantsByRank);
        return $participantsByRank;
    }

    protected function addToRankedMatches(Match $currentMatch, array &$matches)
    {
        $previousMatches = $currentMatch->getPreviousMatches() ?? [];
        foreach ($previousMatches as $match) {
            if ($match->getLoserRank() || $match->getWinnerRank()) {
                $matches[] = $match->id;
                $this->addToRankedMatches($match, $matches);
            }
        }
    }

    /**
     * @return Builder|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function authenticatedUserMatches()
    {
        $user = request()->user();
        if (!$user) {
            return $this->matches()->where('id', 0);
        }
        if ($this->players == 1) {
            $participant = Participant::query()
                ->where('tournament_id', $this->id)
                ->where('participantable_type', User::class)
                ->where('participantable_id', $user->id)
                ->first();
            if (!$participant) {
                return $this->matches()->where('id', 0);
            }
            return $this->matches()
                ->whereHas('plays', function (Builder $plays) use ($participant) {
                    return $plays->whereHas('parties', function ($parties) use ($participant) {
                        return $parties->where('team_id', $participant->id);
                    });
                });
        } else {
            $teamIds = $user->teams()->pluck('teams.id')->all();
            $teamIds[] = 0;
            $participant = Participant::query()
                ->where('tournament_id', $this->id)
                ->where('participantable_type', Team::class)
                ->whereIn('participantable_id', $teamIds)
                ->first();
            if (!$participant) {
                return $this->matches()->where('id', 0);
            }
            return $this->matches()
                ->whereHas('plays', function (Builder $plays) use ($participant) {
                    return $plays->whereHas('parties', function ($parties) use ($participant) {
                        return $parties->where('team_id', $participant->id);
                    });
                });
        }
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeRecentlyFinished(Builder $query)
    {
        $now = \Carbon\Carbon::now();
        return $query->whereNotNull('started_at')
            ->whereNotNull('ended_at')
            ->where('ended_at', '<=', $now)
            ->orderBy('ended_at', 'DESC');

    }

    /**
     * Filter only today tournaments
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeToday(Builder $query)
    {
        $today = getToday();
        return $query->whereNotNull('started_at')
            ->whereDate('started_at', $today)
            ->orderBy('started_at', 'DESC');
    }

    /**
     * Filter live or start today tournaments
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeTodayOrLive(Builder $query)
    {
        $now = \Carbon\Carbon::now();
        $tomorrow = getToday()
            ->startOfDay()
            ->addDay();
        return $query->whereNotNull('started_at')
            ->whereNotNull('ended_at')
            ->where('started_at', '<', $tomorrow)
            ->where('ended_at', '>=', $now)
            ->orderBy('started_at', 'ASC');
    }

    /**
     * Filter upcoming tournaments
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUpcoming(Builder $query)
    {
        $today = getToday();
        return $query->whereNotNull('started_at')
            ->where('started_at', '>', $today)
            ->orderBy('started_at', 'ASC');
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeStartAfterTomorrow(Builder $query)
    {
        $theDayAfterTomorrow = getToday()
            ->startOfDay()
            ->addDays(2);
        $monthLater = getToday()
            ->addMonth();
        return $query->whereNotNull('started_at')
            ->whereDate('started_at', '>=', $theDayAfterTomorrow)
            ->where('started_at', '<=', $monthLater)
            ->orderBy('started_at', 'ASC');
    }

    /**
     * Filter upcoming tournaments from this moment
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUpcomingMoment(Builder $query)
    {
        $now = \Carbon\Carbon::now();
        return $query->whereNotNull('started_at')
            ->where('started_at', '>=', $now)
            ->orderBy('started_at', 'ASC');
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeLaterToday(Builder $query)
    {
        $now = \Carbon\Carbon::now();
        $tomorrow = getToday()
            ->startOfDay()
            ->addDay();
        return $query->whereNotNull('started_at')
            ->where('started_at', '>=', $now)
            ->where('started_at', '<', $tomorrow)
            ->orderBy('started_at', 'ASC');
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeTomorrow(Builder $query)
    {
        $tomorrow = getToday()
            ->startOfDay()
            ->addDay();
        return $query->whereNotNull('started_at')
            ->whereDate('started_at', $tomorrow)
            ->orderBy('started_at', 'ASC');
    }

    /**
     * Filter last month tournaments
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeLastMonth(Builder $query)
    {
        $today = getToday();
        $lastMonth = getToday()
            ->startOfDay()
            ->subMonth();
        return $query->whereNotNull('started_at')
            ->whereDate('started_at', '<', $today)
            ->whereDate('started_at', '>=', $lastMonth)
            ->orderBy('started_at', 'DESC');
    }

    /**
     * Filter tournaments with a live match
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithActiveMatch(Builder $query)
    {
        $now = \Carbon\Carbon::now();
        return $query->whereNotNull('started_at')
            ->where('started_at', '<=', $now)
            ->whereHas('matches', function (Builder $matches) use ($now) {
                $matches->whereNotNull('started_at')
                    ->where('started_at', '<=', $now)
                    ->whereNull('winner_team_id');
            })
            ->orderBy('started_at', 'DESC');
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeLive(Builder $query)
    {
        $now = \Carbon\Carbon::now();
        return $query->whereNotNull('started_at')
            ->whereNotNull('ended_at')
            ->where('started_at', '<=', $now)
            ->where('ended_at', '>=', $now)
            ->orderBy('started_at', 'DESC');
    }

    /**
     * Filter featured tournaments
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeFeatured(Builder $query)
    {
        return $query->where('featured', true)
            ->orderBy('updated_at', 'DESC');
    }

    /**
     * @return bool
     */
    public function getCheckInIsAllowedAttribute()
    {
        if ($this->started_at) {
            if ($this->started_at->diffInSeconds(\Carbon\Carbon::now(), false) > 0) {
                return false;
            } else {
                return $this->allow_check_in
                    || \Carbon\Carbon::now()->diffInMinutes($this->started_at, false) <= $this->check_in_period;
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getRegionTitleAttribute()
    {
        return $this->region()->get()->first()->title;
    }

    /**
     * @return int
     */
    public function getAcceptedCountAttribute()
    {
        return $this->participants()
            ->whereIn('status', [ParticipantAcceptanceState::ACCEPTED, ParticipantAcceptanceState::ACCEPTED_NOT_READY])
            ->count();
    }

    /**
     * @return mixed
     */
    public function getTournamentGameAttribute()
    {
        return $this->game()->get(['title', 'logo'])->first();
    }

    /**
     * @return mixed
     */
    public function getOrganizationLogoAttribute()
    {
        return $this->organization->logo;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function moderators()
    {
        return $this->belongsToMany(User::class, 'tournament_moderators', 'tournament_id', 'user_id');
    }

    /**
     * @return BattleRoyaleEngine|DoubleEliminationEngine|SingleEliminationEngine|null
     */
    public function engine()
    {
        $tournamentType = TournamentType::where('id', $this->tournament_type_id)->first();
        if ($tournamentType->title == 'Single Elimination') {
            return new SingleEliminationEngine($this);
        }
        if ($tournamentType->title == 'Double Elimination') {
            return new DoubleEliminationEngine($this);
        }
        if ($tournamentType->title == 'Battle Royale') {
            return new BattleRoyaleEngine($this);
        }
        return null;
    }

    /**
     * @return bool
     */
    public function numberOfMatchParticipantsIsTwo()
    {
        $tournamentType = TournamentType::where('id', $this->tournament_type_id)->first();
        if ($tournamentType->title == 'Battle Royale') {
            return false;
        }
        return true;
    }

    /**
     * @return array|null
     */
    public function getBracket()
    {
        $engine = $this->engine();
        if ($engine) {
            return $engine->getBracket($this);
        }
        return null;
    }
}
