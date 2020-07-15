<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Prize extends Model
{
    use Actionable;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'rank' => 'integer',
    ];

    /**
     * Get the prize type that owns the prize.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prizeType()
    {
        return $this->belongsTo(PrizeType::class);
    }

    /**
     * Get the tournament that owns the prize.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }
}
