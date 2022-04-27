<?php

namespace App\Nova;

use App\Enums\ProductStatus;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{Avatar, Badge, Boolean, Code, Currency, HasMany, ID, Image, KeyValue, Number, Select, Text};
use Laravel\Nova\Panel;
use App\Nova\ProductImage;

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
        'points',
        'quantity',
    ];

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string|null
     */
    public function subtitle()
    {
        return $this->points . ' Points';
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
     * @param  \Illuminate\Http\Request $request
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

            Text::make('Title')
                ->required()
                ->rules('required', 'max:254'),

            Code::make('Description')
                ->language('markdown')
                ->required()
                ->rules('required'),

            Image::make('Image')
                ->disk('s3')
                ->squared()
                ->path('products')
                ->prunable()
                ->deletable()
                ->nullable()
                ->rules('nullable', 'mimes:jpeg,jpg,png'),

            Boolean::make('Is Featured'),

            Number::make('Points')
                ->min(0)
                ->required()
                ->rules('required', 'integer', 'gte:0'),

            Number::make('Original Points')
                ->min(0)
                ->rules('nullable', 'integer', 'gte:points'),

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
                    ProductStatus::DEACTIVE => 'Deactive',
                    ProductStatus::ACTIVE => 'Active',
                    ProductStatus::COMING_SOON => 'Coming Soon',
                ]),

            Badge::make('Status', function () {
                switch ($this->status) {
                    case ProductStatus::ACTIVE:
                        return 'Active';

                    case ProductStatus::COMING_SOON:
                        return 'Coming Soon';

                    default:
                        return 'Deactive';
                }
            })->map([
                'Deactive' => 'danger',
                'Active' => 'success',
                'Coming Soon' => 'warning',
            ])->exceptOnForms(),

            KeyValue::make('Attributes')
                ->rules('json'),
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
            Hasmany::make('Product Images', 'images', ProductImage::class),
            HasMany::make('Orders'),
        ];
    }
}
