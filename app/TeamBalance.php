<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeamBalance extends Model
{
    protected $fillable = [
        'team_id',
        'tournament_id',
        'points',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }
}
