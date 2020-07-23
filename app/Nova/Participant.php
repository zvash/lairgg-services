<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    BelongsTo,
    DateTime,
    ID,
    MorphMany,
    MorphTo,
    Number
};
use Laravel\Nova\Panel;

class Participant extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Participant::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'seed',
        'rank',
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
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
            (new Panel('Participant Details', $this->details()))->withToolbar(),

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

            DateTime::make('Checked in at')
                ->sortable()
                ->nullable()
                ->rules('nullable', 'date', 'before:now'),

            Number::make('Seed')
                ->sortable()
                ->nullable()
                ->rules('nullable', 'integer', 'gte:1'),

            Number::make('Rank')
                ->sortable()
                ->nullable()
                ->rules('nullable', 'integer', 'gte:1'),
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

            BelongsTo::make('Prize')
                ->showCreateRelationButton()
                ->searchable()
                ->nullable(),

            MorphTo::make('Participantable')
                ->types([
                    User::class,
                    Team::class,
                ])
                ->hideWhenUpdating()
                ->searchable()
                ->withSubtitles()
                ->showCreateRelationButton(),

            MorphMany::make('Transactions'),
        ];
    }
}
