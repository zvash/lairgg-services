<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    Avatar,
    Badge,
    BelongsTo,
    Code,
    Currency,
    HasMany,
    ID,
    Number,
    Select,
    Text
};
use Laravel\Nova\Panel;
use Laravel\Nova\Http\Requests\NovaRequest;

class productImage extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\ProductImage::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

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
        ];
    }
    protected function relations()
    {
        return [
            BelongsTo::make('Product')
        ];
    }

}
