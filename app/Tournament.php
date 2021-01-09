<?php

namespace App;

use App\Enums\Status;
use App\Traits\Eloquents\{
    Joinable,
    Linkable
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
        Joinable;

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
        return $query->whereNotNull('started_at')
            ->whereDate('started_at', date('Y-m-d'))
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
        return $query->whereNotNull('started_at')
            ->whereDate('started_at', '<', date('Y-m-d'))
            ->orderBy('started_at', 'DESC');
    }

    /**
     * Filter last month tournaments
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeLastMonth(Builder $query)
    {
        return $query->whereNotNull('started_at')
            ->whereDate('started_at', '>', date('Y-m-d'))
            ->whereDate('started_at', '>=', date('Y-m-d', strtotime('-30 days')))
            ->orderBy('started_at', 'DESC');
    }
}
