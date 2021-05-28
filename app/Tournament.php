<?php

namespace App;

use App\Enums\ParticipantAcceptanceState;
use App\Enums\Status;
use App\Traits\Eloquents\{
    InviteAware, Joinable, Linkable
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
        InviteAware;

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
        'featured',
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
    ];

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
     * Filter upcoming tournaments
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUpcoming(Builder $query)
    {
        $today = getToday();
        return $query->whereNotNull('started_at')
            ->whereDate('started_at', '>', $today)
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
            ->where('status', ParticipantAcceptanceState::ACCEPTED)
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function moderators()
    {
        return $this->belongsToMany(User::class, 'tournament_moderators', 'tournament_id', 'user_id');
    }
}
