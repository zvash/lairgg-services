<?php

namespace App\Providers;

use App\Organization;
use App\Policies\OrganizationPolicy;
use App\Policies\TournamentPolicy;
use App\Tournament;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Passport\RouteRegistrar;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Organization::class => OrganizationPolicy::class,
        Tournament::class => TournamentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $this->registerPassport();
    }

    /**
     * Setup Laravel Passport.
     *
     * @return void
     */
    protected function registerPassport()
    {
        Passport::routes(function (RouteRegistrar $router) {
            // Handle Client Credential and Password Grants Routes
            $router->forAccessTokens();

            // Handle Refresh Tokens Routes
            $router->forTransientTokens();
        });

        Passport::tokensExpireIn(now()->addweeks(4));
        Passport::refreshTokensExpireIn(now()->addweeks(12));
    }
}
