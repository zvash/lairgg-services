<?php

namespace App\Nova\Actions;

use App\TournamentAnnouncement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;

class MakeAnnouncement extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * @return string
     */
    public function name()
    {
        return 'Make Announcement';
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
        $userId = request()->user()->id;
        $content = $fields->message;
        $announcement = TournamentAnnouncement::query()->create([
            'tournament_id' => $tournament->id,
            'user_id' => $userId,
            'content' => $content
        ]);
        if ($announcement) {
            return Action::message('Announcement was created successfully');
        }
        return Action::danger('There were some issues with creating the announcement.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Message', 'message')
                ->required()
                ->rules('required', 'filled'),

        ];
    }
}
