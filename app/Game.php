<?php

namespace App;

use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use Laravel\Nova\Actions\Actionable;

class Game extends Model
{
    use SoftDeletes, Actionable;

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
        'launched_at' => 'date',
    ];

    /**
     * Get the game type that owns the game.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gameType()
    {
        return $this->belongsTo(GameType::class);
    }

    /**
     * Get the studio that owns the game.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }

    /**
     * Get the teams for the game.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Get the maps for the game.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function maps()
    {
        return $this->hasMany(Map::class);
    }

    /**
     * The users that belong to the game.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'usernames')
            ->using(Username::class)
            ->withPivot('username')
            ->withTimestamps();
    }
}
