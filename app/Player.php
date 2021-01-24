<?php

namespace App;

use App\Team;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Player extends Pivot
{

    protected $table = 'players';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'captain' => 'boolean',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'captain' => false,
    ];

    /**
     * Get Player's team
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get Player's user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a detailed version of the current player
     *
     * @return mixed
     */
    public function detailed()
    {
        return $this->team->players()->where('players.id', $this->id);
    }
}
