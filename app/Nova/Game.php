<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    Avatar,
    Code,
    Date,
    ID,
    Image,
    Text
};
use Laravel\Nova\Panel;

class Game extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Game::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string|null
     */
    public function subtitle()
    {
        return $this->studio->name;
    }

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
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            new Panel('Game Details', $this->details()),

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
                ->path('games/logos')
                ->prunable()
                ->deletable(false)
                ->required()
                ->creationRules('required')
                ->updateRules('nullable')
                ->rules('mimes:jpeg,jpg,png'),

            Image::make('Image')
                ->disk('s3')
                ->squared()
                ->hideFromIndex()
                ->path('games/images')
                ->prunable()
                ->deletable(false)
                ->required()
                ->creationRules('required')
                ->updateRules('nullable')
                ->rules('mimes:jpeg,jpg,png'),

            Image::make('Cover')
                ->disk('s3')
                ->squared()
                ->hideFromIndex()
                ->path('games/covers')
                ->prunable()
                ->deletable(false)
                ->required()
                ->creationRules('required')
                ->updateRules('nullable')
                ->rules('mimes:jpeg,jpg,png'),

            Text::make('Name')
                ->sortable()
                ->required()
                ->rules('required', 'max:254'),

            Code::make('Bio')
                ->hideFromIndex()
                ->language('markdown')
                ->required()
                ->rules('required'),

            Date::make('Launched at')
                ->sortable()
                ->required()
                ->rules('required', 'date')
                ->format('Do MMMM YYYY'),

            Text::make('Website')
                ->required()
                ->hideFromIndex()
                ->rules('required', 'max:254'),
        ];
    }
}
