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

        $router->group(['prefix' => 'users'], function ($router) {

            $router->post('password/reset/email', 'ForgotPasswordController@sendCode')->name('password.reset.email');
            $router->post('password/reset', 'ForgotPasswordController@resetByCode')->name('password.reset');
            $router->post('password/verify-token', 'ForgotPasswordController@verifyToken')->name('password.verify-token');

            $router->post('/register', 'UserController@store');

            $router->get('/verify/{user}', 'VerificationController@verify')->name('verification.verify');

        });

        $router->group(['middleware' => 'auth:api'], function ($router) {

            $router->group(['prefix' => 'users'], function ($router) {

                $router->post('verify/resend', 'VerificationController@resend')->name('verification.resend');

                $router->post('/set-identifiers', 'UserController@setMissingIdentifiers');

            });

            $router->group(['middleware' => 'verified'], function ($router) {

                $router->group(['prefix' => 'users'], function ($router) {

                    $router->get('tournaments/upcoming/few', 'UserController@getLimitedUpcomingTournaments');
                    $router->get('tournaments/upcoming/all', 'UserController@getUpcomingTournaments');
                    $router->get('tournaments', 'UserController@tournaments');

                    $router->post('games/update', 'UserController@updateUserGames');
                    $router->get('games/all', 'UserController@listAllGames');

                    $router->get('authenticated', 'UserController@authenticated');

                });

                $router->group(['prefix' => 'organizations'], function ($router) {

                    $router->get('/{organizationId}/bio', 'OrganizationController@bio');

                    $router->post('/create', 'OrganizationController@create');
                    $router->post('/{organizationId}/edit', 'OrganizationController@edit');

                    $router->get('/all', 'OrganizationController@all');

                    $router->get('/{organizationId}/tournaments', 'OrganizationController@tournaments');
                    $router->post('/{organizationId}/tournaments/create', 'TournamentController@create');
                    $router->post('/{organizationId}/admins/add', 'OrganizationController@addAdmin');
                    $router->post('/{organizationId}/moderators/add', 'OrganizationController@addModerator');


                });

                $router->group(['prefix' => 'banners'], function ($router) {

                    $router->get('/all', 'BannerController@all');

                });

                $router->group(['prefix' => 'tournaments'], function ($router) {

                    $router->get('/featured', 'TournamentController@featured');
                    $router->get('/live', 'TournamentController@live');
                    $router->get('/later-today', 'TournamentController@laterToday');
                    $router->get('/tomorrow', 'TournamentController@tomorrow');
                    $router->get('/after-tomorrow', 'TournamentController@willStartAfterTomorrow');
                    $router->get('/after-now', 'TournamentController@willStartAfterNow');

                    $router->post('/{tournament}/invite', 'TournamentController@invite');

                    $router->post('/{tournament}/edit', 'TournamentController@edit');

                    $router->get('/{tournament}/overview', 'TournamentController@overview');

                    $router->get('/{tournament}/rules', 'TournamentController@rules');

                    $router->post('/{tournament}/allow-check-in', 'TournamentController@allowCheckIn');

                    $router->get('/{tournament}/participants/accepted', 'TournamentController@acceptedParticipants');
                    $router->get('/{tournament}/participants', 'TournamentController@participants');

                    $router->post('/{tournament}/participantable/{participantable}', 'TournamentController@joinParticipantablesToTournament');

                    $router->get('/{tournament}/lobby', 'TournamentController@getLobbyName');

                });

                $router->group(['prefix' => 'teams'], function ($router) {

                    $router->post('create', 'TeamController@store');
                    $router->post('/{team}/invite', 'TeamController@invite');

                    $router->get('/{team}/players', 'TeamController@players');

                });

                $router->group(['prefix' => 'invitations'], function ($router) {
                    $router->post('accept/team', 'InvitationController@joinTeam');
                    $router->post('decline/team', 'InvitationController@declineTeamInvitation');
                    $router->post('accept/tournament', 'InvitationController@joinTournament');
                    $router->post('decline/tournament', 'InvitationController@declineTournamentInvitation');
                });

                $router->group(['prefix' => 'games'], function ($router) {

                    $router->get('/all', 'GameController@all');

                });

                $router->group(['prefix' => 'invitations'], function ($router) {

                    $router->get('/flash', 'InvitationController@flash');
                    $router->post('/flashed', 'InvitationController@flashed');

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

                    $router->get('/{match}/lobby', 'MatchController@getLobbyName');

                });

                $router->group(['prefix' => 'disputes'], function ($router) {

                    $router->post('/{dispute}/close', 'DisputeController@close');

                });

                $router->group(['prefix' => 'lobbies'], function ($router) {

                    $router->get('/{lobbyName}/user', 'LobbyController@getUserByLobbyName');

                });

                $router->group(['prefix' => 'plays'], function ($router) {

                    $router->post('/{play}/update', 'PlayController@update');

                });


            });

        });


    });

});
