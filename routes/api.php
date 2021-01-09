<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$router->group(['prefix' => 'v1'], function ($router) {

    $router->group(['namespace' => 'Api\V1'], function ($router) {

        $router->group(['middleware' => 'auth'], function ($router) {

            $router->group(['prefix' => 'organizations'], function ($router) {

                $router->post('/create', 'OrganizationController@create');
                $router->post('/{organizationId}/edit', 'OrganizationController@edit');

                $router->get('/all', 'OrganizationController@all');

                $router->get('/{organizationId}/tournaments', 'OrganizationController@tournaments');
                $router->post('/{organizationId}/tournaments/create', 'TournamentController@create');
                $router->post('/{organizationId}/admins/add', 'OrganizationController@addAdmin');
                $router->post('/{organizationId}/moderators/add', 'OrganizationController@addModerator');

            });

            $router->group(['prefix' => 'tournaments'], function ($router) {

                $router->post('/{tournament}/edit', 'TournamentController@edit');

                $router->get('/{tournament}/overview', 'TournamentController@overview');

            });

            $router->group(['prefix' => 'games'], function ($router) {

                $router->get('/all', 'GameController@all');

            });


        });


    });

});