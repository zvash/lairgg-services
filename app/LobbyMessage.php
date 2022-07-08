<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LobbyMessage extends Model
{

    protected $fillable = [
        'uuid',
        'lobby_id',
        'user_id',
        'lobby_name',
        'type',
        'sequence',
        'sent_at',
        'message',
    ];

    protected $casts = [
        //'message' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lobby()
    {
        return $this->belongsTo(Lobby::class, 'lobby_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function dispute()
    {
        return $this->hasOne(Dispute::class);
    }
}
