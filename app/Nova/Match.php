<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    BelongsTo,
    Boolean,
    DateTime,
    Number
};
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
        return 'Match: '.$this->tournament->title;
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
            new Panel('Match Details', $this->details()),

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
            Number::make('Round')
                ->nullable()
                ->rules('nullable', 'integer', 'gte:1'),

            Number::make('Group')
                ->nullable()
                ->rules('nullable', 'integer', 'gte:1'),

            Number::make('Play count')
                ->hideFromIndex()
                ->help('BO (aka Best of), like BO5 or BO3 ...')
                ->min(1)
                ->required()
                ->rules('required', 'integer', 'gte:1'),

            Boolean::make('Is forfeit'),

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
                ->showCreateRelationButton()
                ->searchable()
                ->withSubtitles()
                ->required(),

            BelongsTo::make('Winner')
                ->showCreateRelationButton()
                ->nullable()
                ->searchable(),
        ];
    }
}
