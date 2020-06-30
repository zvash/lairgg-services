<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    Avatar,
    Code,
    ID,
    Image,
    Text
};
use Laravel\Nova\Panel;

class Team extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Team::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
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
            new Panel('Team Details', $this->details()),

            new Panel('Modifications', $this->modifications(true)),
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

            Avatar::make('Logo')
                ->disk('s3')
                ->squared()
                ->path('teams/logos')
                ->prunable()
                ->deletable()
                ->nullable()
                ->rules('nullable', 'mimes:jpeg,jpg,png'),

            Image::make('Cover')
                ->disk('s3')
                ->squared()
                ->hideFromIndex()
                ->path('teams/covers')
                ->prunable()
                ->deletable()
                ->nullable()
                ->rules('nullable', 'mimes:jpeg,jpg,png'),

            Text::make('Name')
                ->sortable()
                ->required()
                ->rules('required', 'max:254'),

            Code::make('Bio')
                ->hideFromIndex()
                ->language('markdown')
                ->nullable()
                ->rules('nullable'),

            Text::make('Website')
                ->nullable()
                ->hideFromIndex()
                ->rules('nullable', 'max:254'),
        ];
    }
}
