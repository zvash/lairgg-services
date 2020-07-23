<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    BelongsTo,
    HasOne,
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
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

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
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            (new Panel('Prize Details', $this->details()))->withToolbar(),

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

            Text::make('Title')
                ->sortable()
                ->required()
                ->rules('required', 'max:254'),

            Number::make('Rank')
                ->sortable()
                ->required()
                ->rules('required', 'integer', 'gte:1'),

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
            BelongsTo::make('Value Type', 'valueType')
                ->help('Try `<b>Cash</b>`, `<b>Point</b>` or `<b>Gift</b>` in search box.')
                ->searchable()
                ->required(),

            BelongsTo::make('Tournament')
                ->showCreateRelationButton()
                ->searchable()
                ->withSubtitles()
                ->required(),

            HasOne::make('Participant'),
        ];
    }
}
