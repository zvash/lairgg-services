<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lobby extends Model
{
    protected $fillable = [
        'name',
        'lobby_aware_id',
        'lobby_aware_type',
        'is_active',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function owner()
    {
        return $this->morphTo('lobby_aware');
    }
}
