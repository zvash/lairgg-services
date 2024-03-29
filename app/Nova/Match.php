<?php

namespace App\Nova;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    BelongsTo,
    Boolean,
    DateTime,
    HasMany,
    ID,
    Number
};
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Match extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Match::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'round',
        'group',
        'play_count',
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * The number of results to display in the global search.
     *
     * @var int
     */
    public static $displayInNavigation = false;

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return $this->tournament->title;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            (new Panel('Match Details', $this->details()))->withToolbar(),

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
        return  [
            ID::make()->sortable(),

            Number::make('Round')
                ->sortable()
                ->nullable()
                ->rules('nullable', 'integer', 'gte:1'),

            Number::make('Group')
                ->sortable()
                ->nullable()
                ->rules('nullable', 'integer', 'gte:1'),

            Number::make('Play count')
                ->hideFromIndex()
                ->help('BO (aka Best of), like BO5 or BO3 ...')
                ->min(1)
                ->required()
                ->rules('required', 'integer', 'gte:1'),

            Boolean::make('Is forfeit')
                ->sortable(),

            DateTime::make('Started at')
                ->hideFromIndex()
                ->nullable()
                ->rules('nullable', 'date', 'after:now'),
        ];
    }

    /**
     * Resource relations.
     *
     * @return array
     */
    protected function relations()
    {
        return [
            BelongsTo::make('Tournament')
                ->readonly(),

            BelongsTo::make('Winner', 'winner', Participant::class)
                ->showCreateRelationButton()
                ->nullable()
                ->searchable(),

            HasMany::make('Plays'),
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
        $match = $request->findModelQuery()->first() ?? $request->model();

        $tournament = $match->tournament ?? $request->findParentModel();

        if ($tournament) {
            $query->whereHas('participants', function (Builder $query) use ($match) {
                return $query->whereNotNull('checked_in_at')->whereTournamentId($match->tournament->id);
            });
        }

        return $query->whereHas('parties', function (Builder $query) use ($match) {
            return $query->whereIn('play_id', $match->plays->pluck('id'));
        });
    }
}
