<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Link extends Model
{
    use Actionable;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Get the owning linkable model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function linkable()
    {
        return $this->morphTo();
    }

    /**
     * Get the link type that owns the link.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function linkType()
    {
        return $this->belongsTo(LinkType::class);
    }
}
