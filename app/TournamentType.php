<?php

namespace App;

use App\Enums\TournamentStage;
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use Laravel\Nova\Actions\Actionable;

class TournamentType extends Model
{
    use Actionable, SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * check tournament type is FFA.
     *
     * @return bool
     */
    public function isFFA()
    {
        return $this->checkStage(TournamentStage::FFA());
    }

    /**
     * check tournament type is dual.
     *
     * @return bool
     */
    public function isDual()
    {
        return $this->checkStage(TournamentStage::Dual());
    }

    /**
     * Check tournament type stage.
     *
     * @param  \App\Enums\TournamentStage  $stage
     * @return bool
     */
    public function checkStage(TournamentStage $stage)
    {
        return $this->stage == $stage;
    }

    /**
     * Get the tournaments for the tournaments type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }
}
