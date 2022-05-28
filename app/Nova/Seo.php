<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\{
    Avatar,
    ID,
    KeyValue,
    Line,
    MorphTo,
    Stack,
    Text,
    Textarea
};
use Laravel\Nova\Panel;

class Seo extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Seo::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'title',
        'description',
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
            (new Panel('Seo Details', $this->details()))->withToolbar(),

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

            Avatar::make('Image')
                ->disk('s3')
                ->squared()
                ->path('seos/images')
                ->prunable()
                ->deletable()
                ->nullable()
                ->rules('nullable', 'mimes:jpeg,jpg,png,webp'),

            Text::make('Title')
                ->sortable()
                ->required()
                ->rules('required', 'max:254'),

            Textarea::make('Description')
                ->required()
                ->rules('required', 'max:254'),

            KeyValue::make('Keywords')
                ->keyLabel('ID')
                ->valueLabel('Keyword')
                ->rules('json'),

            Stack::make('Meta', [
                Line::make('Keywords', function () {
                    return Str::limit($this->formatted_keywords, 50);
                })->extraClasses('text-primary-dark')->asSubTitle(),

                Line::make('Description', function () {
                    return Str::limit($this->description, 50);
                })->asSmall(),
            ])->onlyOnIndex(),
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
            MorphTo::make('Seoable')
                ->required()
                ->types([
                    Page::class,
                    Game::class,
                ]),
        ];
    }
}
