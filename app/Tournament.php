<?php

namespace App;

use App\Enums\Status;
use App\Traits\Eloquents\{
    Joinable,
    Linkable
};
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use Laravel\Nova\Actions\Actionable;

class Tournament extends Model
{
    use Actionable,
        SoftDeletes,
        Linkable,
        Joinable;

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
        'unlisted' => 'boolean',
        'invite_only' => 'boolean',
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
        'unlisted' => false,
        'invite_only' => true,
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
}
