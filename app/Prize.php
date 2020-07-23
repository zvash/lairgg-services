<?php

namespace App;

use App\Casts\Value;
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
        'value' => Value::class,
    ];

    /**
     * Get the value type that owns the prize.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function valueType()
    {
        return $this->belongsTo(ValueType::class);
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

    /**
     * Get the participant associated with the prize.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function participant()
    {
        return $this->hasOne(Participant::class);
    }
}
