<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    /**
     * Who has issued this dispute
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function issuer()
    {
        return $this->belongsTo(User::class);
    }
}
