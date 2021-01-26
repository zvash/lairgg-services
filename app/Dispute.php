<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property Play play
 */

class Dispute extends Model
{
    protected $fillable = [
        'play_id',
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
     * Get the play for this dispute
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function play()
    {
        return $this->belongsTo(Play::class);
    }
}
