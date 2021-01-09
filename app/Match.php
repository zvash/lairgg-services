<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

/**
 * @property int group
 * @property int round
 */
class Match extends Model
{
    use Actionable;

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
}
