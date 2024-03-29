<?php

namespace App;

use App\Traits\Eloquents\{
    Linkable,
    Seoable
};
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use Laravel\Nova\Actions\Actionable;

class Game extends Model
{
    use SoftDeletes, Actionable, Linkable, Seoable;

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

    /**
     * Get the tournaments for the game.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function coinTossReason()
    {
        return $this->hasMany(CoinTossReason::class);
    }
}
