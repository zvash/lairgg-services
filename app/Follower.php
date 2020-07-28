<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'followed_at' => 'datetime',
    ];

    /**
     * Get the owning followable model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function followable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the follower.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
