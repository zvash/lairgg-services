<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CoinTossReason extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
}
