<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TournamentModerator extends Model
{
    protected $fillable = [
        'tournament_id',
        'user_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function moderator()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }
}
