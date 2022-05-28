<?php

namespace App\Nova\Actions;

use App\Nova\Tournament;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ReleaseRandomBracket extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * @return string
     */
    public function name()
    {
        return 'Release Random Bracket';
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
        $model = $models->all()[0];
        if ($model->engine()->createBracket()) {
            return Action::message('A new bracket was created and released successfully.');
        }
        return Action::danger('Could not create a new bracket for this tournament.');
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
