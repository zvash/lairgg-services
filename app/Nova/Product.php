<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    Avatar,
    Badge,
    Code,
    Currency,
    HasMany,
    ID,
    Number,
    Select,
    Text
};
use Laravel\Nova\Panel;

class Product extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Product::class;

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
        'price',
        'point',
        'quantity',
    ];

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string|null
     */
    public function subtitle()
    {
        return $this->price ? '$'.$this->price : $this->point.' Points';
    }

    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return 'Shop';
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
            (new Panel('Product Details', $this->details()))->withToolbar(),

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

            Avatar::make('Image')
                ->disk('s3')
                ->squared()
                ->path('products/images')
                ->prunable()
                ->deletable(false)
                ->required()
                ->creationRules('required')
                ->updateRules('nullable')
                ->rules('mimes:jpeg,jpg,png'),

            Text::make('Title')
                ->required()
                ->rules('required', 'max:254'),

            Code::make('description')
                ->language('markdown')
                ->required()
                ->rules('required'),

            Currency::make('Price')
                ->currency('USD')
                ->required()
                ->rules('required', 'numeric', 'gte:0'),

            Number::make('Point')
                ->min(0)
                ->required()
                ->rules('required', 'integer', 'gte:0'),

            Number::make('Quantity')
                ->min(0)
                ->required()
                ->rules('required', 'integer', 'gte:0'),

            Select::make('Status')
                ->displayUsingLabels()
                ->onlyOnForms()
                ->required()
                ->rules('required')
                ->options([
                    0 => 'Deactive',
                    1 => 'Active',
                ]),

            Badge::make('Status', function () {
                return $this->status ? 'Active' : 'Deactivated';
            })->map([
                'Deactivated' => 'danger',
                'Active' => 'success',
            ])->exceptOnForms(),
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
            HasMany::make('Orders'),
        ];
    }
}
