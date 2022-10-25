<?php

namespace App\Nova\Actions;

use App\Repositories\TournamentRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ReleaseTournamentGems extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * @return string
     */
    public function name()
    {
        return 'Release Gems';
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $tournament = $models->all()[0];
        $repository = new TournamentRepository();
        return Action::message($repository->releaseGems($tournament));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
