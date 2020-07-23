<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    HasMany,
    ID,
    Text
};
use Laravel\Nova\Panel;

class ValueType extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\ValueType::class;

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
    ];

    /**
     * Determine if the current user can view the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $ability
     * @return bool
     */
    public function authorizedTo(Request $request, $ability)
    {
        return in_array($ability, ['view'])
            ? parent::authorizedTo($request, $ability)
            : false;
    }

    /**
     * Determine if the current user can create new resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return 'Types';
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
            new Panel('Value Type Details', $this->details()),

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

            Text::make('Title')->sortable(),
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
            HasMany::make('Prizes'),
        ];
    }
}
