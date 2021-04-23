<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SocialMediaAccount extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
