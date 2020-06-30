<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    Avatar,
    ID,
    Text
};
use Laravel\Nova\Panel;

class Studio extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Studio::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

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
        'name',
        'website',
    ];

    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return 'Games';
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
            new Panel('Studio Details', $this->details()),

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
                ->path('studios/logos')
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

            Text::make('website')
                ->hideFromIndex()
                ->required()
                ->rules('required', 'max:254'),
        ];
    }
}
