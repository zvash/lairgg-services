<?php

namespace Standard\Theme;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::theme(asset('/standard/theme/theme.css'));

        $this->publishes([
            __DIR__.'/../resources/css' => public_path('standard/theme'),
        ], 'standrad-theme');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
