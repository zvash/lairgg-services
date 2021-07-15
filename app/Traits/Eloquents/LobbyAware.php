<?php

namespace App\Traits\Eloquents;

use App\Lobby;

trait LobbyAware
{
    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function lobby()
    {
        return $this->morphOne(Lobby::class, 'lobby_aware');
    }
}
