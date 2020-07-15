<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    BelongsTo,
    ID,
    Number,
    Text
};
use Laravel\Nova\Panel;

class Prize extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Prize::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'title',
        'rank',
        'value',
    ];

    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return 'Tournaments';
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
            new Panel('Prize Details', $this->details()),

            new Panel('Modifications', $this->modifications(true)),

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

            Text::make('Title')
                ->sortable()
                ->required()
                ->rules('required', 'max:254'),

            Number::make('Rank')
                ->sortable()
                ->required()
                ->rules('required', 'gte:1'),

            Text::make('Value')
                ->hideFromIndex()
                ->required()
                ->rules('required', 'max:254'),
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
            BelongsTo::make('Prize Type', 'prizeType')
                ->showCreateRelationButton()
                ->searchable()
                ->required(),

            BelongsTo::make('Tournament')
                ->showCreateRelationButton()
                ->searchable()
                ->withSubtitles()
                ->required(),
        ];
    }
}
