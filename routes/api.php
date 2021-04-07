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

                $router->post('/{tournament}/allow-check-in', 'TournamentController@allowCheckIn');

                $router->get('/{tournament}/participants', 'TournamentController@participants');

            });

            $router->group(['prefix' => 'games'], function ($router) {

                $router->get('/all', 'GameController@all');

            });

            $router->group(['prefix' => 'participants'], function ($router) {

                $router->post('/{participant}/check-in', 'ParticipantController@checkParticipantIn');

                $router->get('/{participant}/players', 'ParticipantController@players');

            });

            $router->group(['prefix' => 'players'], function ($router) {

                $router->get('/{player}/get', 'PlayerController@get');

            });

            $router->group(['prefix' => 'matches'], function ($router) {

                $router->get('/{match}/get', 'MatchController@get');

                $router->post('/{match}/play-count', 'MatchController@setPlayCount');

                $router->get('/{match}/disputes', 'MatchController@getDisputes');

            });

            $router->group(['prefix' => 'disputes'], function ($router) {

                $router->post('/{dispute}/close', 'DisputeController@close');

            });

            $router->group(['prefix' => 'plays'], function ($router) {

                $router->post('/{play}/update', 'PlayController@update');

            });

        });


    });

});
