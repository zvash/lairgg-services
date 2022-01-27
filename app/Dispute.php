<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property Play play
 */

class Dispute extends Model
{
    protected $fillable = [
        'match_id',
        'lobby_message_id',
        'issued_by',
        'text',
        'screenshot',
    ];
    /**
     * Who has issued this dispute
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function issuer()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the match for this dispute
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function match()
    {
        return $this->belongsTo(Match::class);
    }

    /**
     * Get the match for this dispute
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lobbyMessage()
    {
        return $this->belongsTo(LobbyMessage::class);
    }
}
