<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Join extends Model
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
        'via_url' => 'boolean',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'via_url' => false,
    ];

    /**
     * Get the owning joinable model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function joinable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the join.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
