<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->observers();
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
                ->withAuthenticationRoutes()
                ->withPasswordResetRoutes()
                ->register();
    }

    /**
     * Configure the Nova authorization services.
     *
     * @return void
     */
    protected function authorization()
    {
        $this->gate();

        Nova::auth(function ($request) {
            return Gate::check('viewNova', [$request->user()]);
        });
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return in_array($user->email, [
                'hossein@edoramedia.com',
                'ali.shafiee@edoramedia.com',
                'ilyad@edoramedia.com',
                'farbod@edoramedia.com',
                'siavash.hekmatnia@gmail.com',
                'siavash@lair.gg',
                'ace@lair.gg'
            ]);
        });
    }

    /**
     * Get the cards that should be displayed on the default Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        return [
            new Help,
        ];
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [];
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

    /**
     * Register nova observers.
     *
     * @return void
     */
    public function observers()
    {
        Nova::serving(function () {
            \App\Link::observe(\App\Observers\Nova\LinkObserver::class);
            \App\Staff::observe(\App\Observers\Nova\StaffObserver::class);
            \App\Match::observe(\App\Observers\Nova\MatchObserver::class);
            \App\Order::observe(\App\Observers\Nova\OrderObserver::class);
            \App\Participant::observe(\App\Observers\Nova\ParticipantObserver::class);
        });
    }
}
