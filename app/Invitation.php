<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = [
        'invited_by',
        'organization_id',
        'invite_aware_type',
        'invite_aware_id',
        'email',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function inviteAware()
    {
        return $this->morphTo();
    }
}
