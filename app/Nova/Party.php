<?php

namespace App\Nova;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    BelongsTo,
    Boolean,
    ID,
    Number
};
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Party extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Party::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'score',
        'is_winner',
        'is_host',
    ];

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            (new Panel('Party Details', $this->details()))->withToolbar(),

            new Panel('Modifications', $this->modifications()),

            new Panel('Relations', $this->relations()),
        ];
    }

    /**
     * Resource detail fields.
     *
     * @return array
     */
    protected function details()
    {
        return [
            ID::make()->sortable(),

            Number::make('Score')
                ->sortable()
                ->min(0)
                ->nullable()
                ->rules('nullable', 'integer', 'gte:0'),

            Boolean::make('Is winner')
                ->sortable(),

            Boolean::make('Is host')
                ->help('Determine which team is Home or Away.')
                ->sortable(),
        ];
    }

    /**
     * Resource relations.
     *
     * @return array
     */
    protected function relations()
    {
        return  [
            BelongsTo::make('Play')
                ->readonly(),

            BelongsTo::make('Team')
                ->searchable()
                ->showCreateRelationButton()
                ->withSubtitles()
                ->nullable(),
        ];
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function relatableTeams(NovaRequest $request, $query)
    {
        $tournament = $request->findParentModelOrFail();

        return $query->whereHas('participants', function (Builder $query) use ($tournament) {
            return $query->whereNotNull('checked_in_at')->whereTournamentId($tournament->id);
        });
    }
}
