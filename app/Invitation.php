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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by')
            ->select(['id', 'username', 'avatar', 'cover']);
    }
}
