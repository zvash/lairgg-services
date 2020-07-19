<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Match extends Model
{
    use Actionable;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = false;

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
        'round' => null,
        'group' => null,
        'started_at' => null,
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
        return $this->belongsTo(Team::class, 'winner_team_id');
    }
}
