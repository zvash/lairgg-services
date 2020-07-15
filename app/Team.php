<?php

namespace App;

use App\Traits\Eloquents\Linkable;
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use Laravel\Nova\Actions\Actionable;

class Team extends Model
{
    use SoftDeletes, Actionable, Linkable;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Get the game that owns the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * The players that belong to the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function players()
    {
        return $this->belongsToMany(User::class, 'players')
            ->using(Player::class)
            ->withTimestamps()
            ->withPivot('captain');
    }

    /**
     * Get all of the team's participants.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function participants()
    {
        return $this->morphMany(Participant::class, 'participantable');
    }
}
