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

        $router->get('/client-version/{type}/{version}', 'ClientVersionController@updateStatus');

        $router->post('users/device/register', 'UserController@registerDeviceForPushNotification');
        $router->get('tournaments/{tournament}/matches/brackets/{bracket}', 'TournamentController@bracketMatches');

        $router->get('countries', 'CountryController@all');

        $router->get('password-criteria', 'UserController@passwordCriteria');
        $router->get('users/{attributeName}/{attributeValue}/check-unique', 'UserController@checkUnique');

        $router->group(['prefix' => 'users'], function ($router) {

            $router->post('password/reset/email', 'ForgotPasswordController@sendCode')->name('password.reset.email');
            $router->post('password/reset', 'ForgotPasswordController@resetByCode')->name('password.reset');
            $router->post('password/verify-token', 'ForgotPasswordController@verifyToken')->name('password.verify-token');

            $router->post('/register', 'UserController@store');

            $router->get('/verify/{user}', 'VerificationController@verify')->name('verification.verified');

        });

        $router->get('teams/{token}/join', 'TeamController@joinByUrlRequest')->name('teams.join_url');

        $router->group(['middleware' => 'auth:api'], function ($router) {

            $router->group(['prefix' => 'users'], function ($router) {

                $router->post('verify/resend', 'VerificationController@resend')->name('verification.resend');

                $router->post('/set-identifiers', 'UserController@setMissingIdentifiers');

                $router->post('/logout', 'UserController@logout');

                $router->get('/search', 'UserController@search');

                $router->post('delete', 'UserController@delete');

            });

            $router->group(['middleware' => 'verified'], function ($router) {

                $router->group(['prefix' => 'users'], function ($router) {

                    $router->get('tournaments/{tournament}/teams', 'UserController@teamsForTournament');
                    $router->get('tournaments/upcoming/few', 'UserController@getLimitedUpcomingTournaments');
                    $router->get('tournaments/upcoming/all', 'UserController@getUpcomingTournaments');
                    $router->get('tournaments', 'UserController@tournaments');
                    $router->get('tournaments/{tournament}/announcements', 'UserController@getTournamentAnnouncements');
                    $router->get('tournaments/{tournament}/announcements/unread-count', 'UserController@getTournamentAnnouncementsUnreadCount');

                    $router->post('games/update', 'UserController@updateUserGames');
                    $router->post('games/{game}/add', 'UserController@addGame');
                    $router->post('games/{game}/remove', 'UserController@removeGame');
                    $router->get('games/all', 'UserController@listAllGames');

                    $router->get('teams/all', 'UserController@listAllTeams');

                    $router->get('matches/all', 'UserController@matches');

                    $router->get('matches/next', 'UserController@getNextMatch');

                    $router->get('orders/{status}/get', 'UserController@listOrders');

                    $router->get('authenticated', 'UserController@authenticated');

                    $router->post('profile/change-password', 'UserController@changePassword');
                    $router->post('profile/update', 'UserController@update');
                    $router->post('profile/images/remove', 'UserController@removeImages');

                    $router->get('genders', 'UserController@genders');

                });

                $router->group(['prefix' => 'notifications'], function ($router) {
                    $router->get('/all', 'PushNotificationController@all');
                    $router->get('/unread-count', 'PushNotificationController@unreadCount');
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
                    $router->get('/finished', 'TournamentController@recentlyFinishedForUser');
                    $router->get('/today', 'TournamentController@today');
                    $router->get('/later-today', 'TournamentController@laterToday');
                    $router->get('/tomorrow', 'TournamentController@tomorrowForUser');
                    $router->get('/after-tomorrow', 'TournamentController@willStartAfterTomorrow');
                    $router->get('/upcoming', 'TournamentController@upcomingForUser');
                    $router->get('/after-now', 'TournamentController@willStartAfterNow');

//                    $router->get('/tomorrow', 'TournamentController@tomorrow');
//                    $router->get('/upcoming', 'TournamentController@upcoming');
//                    $router->get('/finished', 'TournamentController@recentlyFinished');

                    $router->post('/{tournament}/invite', 'TournamentController@invite');

                    $router->post('/{tournament}/edit', 'TournamentController@edit');

                    $router->post('/{tournament}/bracket/create', 'TournamentController@createBracket');

                    $router->get('/{tournament}/organizer-overview', 'TournamentController@organizerOverview');

                    $router->get('/{tournament}/overview', 'TournamentController@overview');
                    $router->get('/{tournament}/prizes', 'TournamentController@prizes');
                    //$router->get('/{tournament}/matches/brackets/{bracket}', 'TournamentController@bracketMatches');
                    $router->get('/{tournament}/matches/brackets/{bracket}/rounds', 'TournamentController@rounds');
                    $router->get('/{tournament}/matches/brackets/{bracket}/rounds/{round}', 'TournamentController@matches');

                    $router->get('/{tournament}/rules', 'TournamentController@rules');

                    $router->post('/{tournament}/allow-check-in', 'TournamentController@allowCheckIn');

                    $router->post('/{tournament}/participants/status/{status}', 'TournamentController@updateParticipantStatus');
                    $router->get('/{tournament}/participants/accepted', 'TournamentController@acceptedParticipants');
                    $router->get('/{tournament}/participants', 'TournamentController@participants');
                    $router->get('/{tournament}/participants/{participantableId}/get', 'TournamentController@getParticipant');

                    $router->post('/{tournament}/participantable/{participantable}', 'TournamentController@joinParticipantablesToTournament');

                    $router->get('/{tournament}/lobby', 'TournamentController@getLobbyName');

                    $router->post('/{tournament}/join', 'TournamentController@joinRequest');
                    $router->post('/{tournament}/leave', 'TournamentController@leaveTournament');
                    $router->post('/{tournament}/check-in', 'TournamentController@checkInTournament');

                });

                $router->group(['prefix' => 'teams'], function ($router) {

                    $router->post('create', 'TeamController@store');
                    $router->post('/{team}/update', 'TeamController@update');
                    $router->post('/{team}/images/remove', 'TeamController@removeImages');
                    $router->post('/{team}/invite', 'TeamController@invite');

                    $router->get('/{team}/players', 'TeamController@players');
                    $router->get('{team}/get', 'TeamController@get');

                    $router->get('{team}/info', 'TeamController@specificTeamInfo');
                    $router->get('{team}/overview', 'TeamController@overview');
                    $router->get('{team}/tournaments', 'TeamController@tournaments');
                    $router->get('{team}/awards', 'TeamController@awards');

                    $router->post('{team}/promote', 'TeamController@promoteToCaptain');
                    $router->post('{team}/remove', 'TeamController@removeFromTeam');
                    $router->post('{team}/leave', 'TeamController@leaveTeam');
                    $router->post('{team}/delete', 'TeamController@deleteTeam');

                    $router->get('{team}/get-join-url', 'TeamController@getJoinUrl');
                    $router->post('{team}/set-join-url', 'TeamController@setJoinUrl');
                    $router->post('/join-by-token', 'TeamController@joinByUrl');

                    $router->get('{team}/search', 'TeamController@searchUser');
                    $router->post('{team}/cancel-invitation', 'TeamController@cancelInvitation');

                    $router->post('{team}/share-gems', 'TeamController@shareGems');

                });

                $router->group(['prefix' => 'players'], function ($router) {

                    $router->get('{user}/info', 'UserController@specificPlayerInfo');
                    $router->get('{user}/about', 'UserController@about');
                    $router->get('{user}/tournaments', 'UserController@playerTournaments');
                    $router->get('{user}/awards', 'UserController@awards');
                    $router->get('{user}/teams', 'UserController@playerTeams');

                });

                $router->group(['prefix' => 'invitations'], function ($router) {
                    $router->post('accept/team', 'InvitationController@joinTeam');
                    $router->post('decline/team', 'InvitationController@declineTeamInvitation');
                    $router->post('accept/tournament', 'InvitationController@joinTournament');
                    $router->post('decline/tournament', 'InvitationController@declineTournamentInvitation');

                    $router->get('count', 'InvitationController@count');
                    $router->get('/unanswered/{type}/all', 'InvitationController@getInvitations');
                });

                $router->group(['prefix' => 'games'], function ($router) {

                    $router->get('/all', 'GameController@all');

                });

                $router->group(['prefix' => 'links'], function ($router) {

                    $router->get('/types', 'LinkController@types');

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

                    $router->post('/{match}/forfeit-all', 'MatchController@forfeitAll');

                    $router->get('/{match}/disputes', 'MatchController@getDisputes');

                    $router->get('/{match}/lobby', 'MatchController@getLobbyName');

                    $router->get('/{match}/overview', 'MatchController@overview');
                    $router->post('/{match}/ready', 'MatchController@setReady');

                });

                $router->group(['prefix' => 'disputes'], function ($router) {

                    $router->post('/{dispute}/close', 'DisputeController@close');

                });

                $router->group(['prefix' => 'products'], function ($router) {

                    $router->get('/{product}/get', 'ShopController@getProductById');
                    $router->get('/list', 'ShopController@products');

                });

                $router->group(['prefix' => 'orders'], function ($router) {

                    $router->post('/register', 'ShopController@storeOrder');
                    $router->get('/{order}/get', 'ShopController@getOrder');

                });

                $router->group(['prefix' => 'shop'], function ($router) {

                    $router->get('/countries', 'ShopController@getCountries');

                });

                $router->group(['prefix' => 'lobbies'], function ($router) {

                    $router->get('/{lobbyName}/user', 'LobbyController@getUserByLobbyName');
                    $router->get('/{lobbyName}/latest', 'LobbyController@latest');
                    $router->get('/{lobbyName}/uuid/{uuid}', 'LobbyController@loadPrevious');
                    $router->get('/{lobbyName}/uuid/{uuid}/latest', 'LobbyController@loadNext');

                    $router->get('/coin-toss-reasons', 'LobbyController@getCoinTossReasons');

                    $router->get('/{lobbyName}/coin-toss-reasons', 'LobbyController@getLobbyCoinTossReasons');

                    $router->post('/{lobbyName}/dispute', 'LobbyController@createDispute');

                    $router->post('/{lobbyName}/coin', 'LobbyController@createCoinToss');

                    $router->post('/{lobbyName}/coin-toss/{uuid}/accept', 'LobbyController@acceptCoinToss');
                    $router->post('/{lobbyName}/coin-toss/{uuid}/decline', 'LobbyController@declineCoinToss');

                    $router->post('/{lobbyName}/picknban/ban/maps/{mapId}', 'LobbyController@banMap');
                    $router->post('/{lobbyName}/picknban/pick/maps/{mapId}', 'LobbyController@pickMap');
                    $router->post('/{lobbyName}/picknban/side/maps/{mapId}/{mode}', 'LobbyController@selectSide');

                });

                $router->group(['prefix' => 'plays'], function ($router) {

                    $router->post('/{play}/update', 'PlayController@update');

                    $router->post('/{play}/score', 'PlayController@setScore');

                });

                $router->group(['prefix' => 'cash-outs'], function ($router) {

                    $router->get('/{cashOut}/get', 'CashOutController@get');

                    $router->get('/rate', 'CashOutController@getExchangeRate');

                    $router->post('/create', 'CashOutController@createCashOut');

                });

                $router->group(['prefix' => 'order-requests'], function ($router) {

                    $router->get('/all', 'OrderRequestController@all');

                });

                $router->get('/search', 'SearchController@search');


            });

        });


    });

});
