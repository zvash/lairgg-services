<?php

namespace App\Http\Controllers\Api\V1;

use App\Game;
use App\Gender;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteProfileImagesRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\RegisterDeviceRequest;
use App\Http\Requests\SetIdentifiersRequest;
use App\Http\Requests\StoreUser;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Repositories\GameRepository;
use App\Repositories\LobbyRepository;
use App\Repositories\TournamentRepository;
use App\Repositories\UserRepository;
use App\Tournament;
use App\Traits\Responses\ResponseMaker;
use App\User;
use App\UserNotificationToken;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ResponseMaker;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUser $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     *
     */
    public function store(StoreUser $request)
    {
        $validated = $request->validated();

        $resource = new UserResource(
            $this->dispatchUserJobs(User::register($validated))
        );

        return $this->success($resource);
    }

    /**
     * @param UpdateUserRequest $request
     * @param UserRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, UserRepository $repository)
    {
        $user = $repository->updateProfile($request);
        return $this->success($user);
    }

    /**
     * @param DeleteProfileImagesRequest $request
     * @param UserRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function removeImages(DeleteProfileImagesRequest $request, UserRepository $repository)
    {
        $user = $repository->deleteProfileImages($request);
        return $this->success($user);
    }

    /**
     * @param ChangePasswordRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        if (! Hash::check($validated['current_password'], $user->password)) {
            return $this->failMessage(__('strings.password.wrong_current_password'), 400);
        }
        $user->password = bcrypt($validated['password']);
        $user->save();
        $tokens = $user->tokens;
        $currentToken = $user->token();
        foreach ($tokens as $token) {
            if ($token->id != $currentToken->id) {
                $token->revoke();
            }
        }
        $currentBearerToken = $request->bearerToken();
        UserNotificationToken::query()
            ->where('user_id', $user->id)
            ->where('passport_token', '<>', $currentBearerToken)
            ->delete();
        return $this->success(['message' => __('strings.password.password_was_changed')]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        UserNotificationToken::query()
            ->where('passport_token', $token)
            ->delete();
        \auth()->user()->token()->revoke();
        return $this->success(['message' => __('strings.user.logged_out')]);
    }

    /**
     * @param SetIdentifiersRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function setMissingIdentifiers(SetIdentifiersRequest $request)
    {
        $validated = collect($request->validated())
            ->only(['email', 'username'])
            ->toArray();

        $user = Auth::user();
        try {
            $setKeys = $this->setMissingFields($validated, $user);
            $user->save();
            if (in_array('email', $setKeys)) {
                $this->dispatchUserJobs($user);
            }
            return $this->success(new UserResource($user));
        } catch (\Exception $exception) {
            return $this->failMessage($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param RegisterDeviceRequest $request
     * @return \Illuminate\Http\Response
     */
    public function registerDeviceForPushNotification(RegisterDeviceRequest $request)
    {
        $validated = $request->validated();
        $userNotificationToken = UserNotificationToken::query()
            ->firstOrCreate([
                'token' => $validated['token'],
            ],[
                'platform' => $validated['platform'],
                'registered_at' => Carbon::now(),
            ]);
        $user = auth()->guard('api')->user();
        if ($user) {
            $userNotificationToken->user_id = $user->id;
            $userNotificationToken->passport_token = $request->bearerToken();
        }
        $userNotificationToken->registered_at = Carbon::now();
        $userNotificationToken->save();
        return response()->noContent();
    }

    /**
     * @param Request $request
     * @param UserRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function search(Request $request, UserRepository $repository)
    {
        $identifier = $request->get('identifier');
        return $this->success($repository->search($identifier));
    }

    /**
     * @param Request $request
     * @param UserRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(DeleteUserRequest $request, UserRepository $repository)
    {
        $user = $request->user();
        $validated = $request->validated();
        $repository->deactivate($user, $validated['reason']);
        $userTokens = $user->tokens;
        \auth()->user()->token()->revoke();
        foreach($userTokens as $token) {
            $token->revoke();
        }
        UserNotificationToken::query()
            ->where('user_id', $user->id)
            ->delete();
        return $this->success(['message' => __('strings.user.user_was_deleted')]);
    }

    /**
     * Get user tournaments
     *
     * @param Request $request
     * @param TournamentRepository $tournamentRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function tournaments(Request $request, TournamentRepository $tournamentRepository)
    {
        $user = Auth::user();
        $tournaments = $tournamentRepository->getUserTournaments($user, 10);
        return $this->success($tournaments);
    }

    /**
     * @param Request $request
     * @param UserRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function matches(Request $request, UserRepository $repository)
    {
        $user = $request->user();
        return $this->success($repository->getUserTournamentsWithMatches($user));
    }

    /**
     * @param Request $request
     * @param Tournament $tournament
     * @param UserRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getTournamentAnnouncements(Request $request, Tournament $tournament, UserRepository $repository)
    {
        $user = $request->user();
        return $this->success($repository->getTournamentAnnouncements($user, $tournament));
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function genders()
    {
        return $this->success(Gender::all());
    }

    /**
     * @param Request $request
     * @param Tournament $tournament
     * @param UserRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getTournamentAnnouncementsUnreadCount(Request $request, Tournament $tournament, UserRepository $repository)
    {
        $user = $request->user();
        return $this->success(['count' => $repository->getTournamentAnnouncementsUnreadCount($user, $tournament)]);
    }

    /**
     * User all upcoming tournaments
     *
     * @param Request $request
     * @param TournamentRepository $tournamentRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getUpcomingTournaments(Request $request, TournamentRepository $tournamentRepository)
    {
        $user = Auth::user();
        $tournaments = $tournamentRepository->getUserUpcomingTournaments($user, 10);
        return $this->success($tournaments);
    }

    /**
     * @param Request $request
     * @param TournamentRepository $tournamentRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getLimitedUpcomingTournaments(Request $request, TournamentRepository $tournamentRepository)
    {
        $user = Auth::user();
        $limit = $request->exists('limit') ? $request->get('limit') : 1;
        if (is_numeric($limit)) {
            $limit = max(1, intval($limit));
        } else {
            $limit = 1;
        }
        $limit = min(10, $limit);
        $tournaments = $tournamentRepository->getUserFirstFewUpcomingTournaments($user, $limit);
        return $this->success($tournaments);
    }

    /**
     * @param Request $request
     * @param GameRepository $gameRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updateUserGames(Request $request, GameRepository $gameRepository)
    {
        $request->validate([
            'game_ids' => 'required|array|min:3',
            'game_ids.*' => 'int|min:1|exists:games,id'
        ]);
        $user = Auth::user();
        if ($user) {
            $inputs = $request->all();
            $userGames = $gameRepository->syncUserGames($user, $inputs['game_ids']);
            return $this->success($userGames);
        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @param Game $game
     * @param GameRepository $gameRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function addGame(Request $request, Game $game, GameRepository $gameRepository)
    {
        $user = Auth::user();
        if ($user) {
            $userGames = $gameRepository->addUserGame($user, $game->id);
            return $this->success($userGames);
        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @param Game $game
     * @param GameRepository $gameRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function removeGame(Request $request, Game $game, GameRepository $gameRepository)
    {
        $user = Auth::user();
        if ($user) {
            try {
                $userGames = $gameRepository->removeUserGame($user, $game->id);
                return $this->success($userGames);
            } catch (\Exception $exception) {
                return  $this->failMessage($exception->getMessage(), 400);
            }

        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function authenticated(Request $request)
    {
        $user = $request->user();
        $user->points = $user->availablePoints();
        return $this->success(['user' => $user]);

    }

    /**
     * @param Request $request
     * @param GameRepository $gameRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function listAllGames(Request $request, GameRepository $gameRepository)
    {
        $user = Auth::user();
        if ($user) {
            $allGames = $gameRepository->getAllGamesAndUserSelectionStatus($user);
            return $this->success($allGames);
        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function listAllTeams(Request $request)
    {
        $user = $request->user();
        if ($user) {
            return $this->success($user->teams->load('players'));
        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @param Tournament $tournament
     * @param TournamentRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function teamsForTournament(Request $request, Tournament $tournament, TournamentRepository $repository)
    {
        $user = $request->user();
        return $this->success($repository->getUserTeamsForTournament($user, $tournament));
    }

    /**
     * @param Request $request
     * @param string $status
     * @param UserRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function listOrders(Request $request, string $status, UserRepository $repository)
    {
        $user = $request->user();
        return $this->success($repository->getUserOrders($user, $status));
    }

    /**
     * @param Request $request
     * @param User $user
     * @param UserRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function about(Request $request, User $user, UserRepository $repository)
    {
        return $this->success($repository->about($user));
    }

    /**
     * @param Request $request
     * @param User $user
     * @param UserRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function awards(Request $request, User $user, UserRepository $repository)
    {
        return $this->success($repository->awards($user));
    }

    /**
     * @param Request $request
     * @param User $user
     * @param UserRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function playerTeams(Request $request, User $user, UserRepository $repository)
    {
        return $this->success($repository->teams($user));
    }

    /**
     * @param Request $request
     * @param User $user
     * @param UserRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function playerTournaments(Request $request, User $user, UserRepository $repository)
    {
        return $this->success($repository->getUserTournamentsWithMatches($user));
    }

    /**
     * @param Request $request
     * @param User $user
     * @param UserRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function specificPlayerInfo(Request $request, User $user, UserRepository $repository)
    {
        return $this->success($repository->info($user));
    }

    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @param LobbyRepository $lobbyRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getNextMatch(Request $request, UserRepository $userRepository, LobbyRepository $lobbyRepository)
    {
        try {
            return $this->success($userRepository->getFirstMatchThatNeedsAttention($request->user(), $lobbyRepository));
        } catch (\Exception $exception) {
            return $this->failNotFound();
        }
    }

    /**
     * Dispatch user jobs and events.
     *
     * @param  \App\User $user
     * @return \App\User
     */
    protected function dispatchUserJobs(User $user)
    {
        event(new Registered($user));


        return $user;
    }

    /**
     * @param array $params
     * @param $user
     * @return array
     * @throws \Exception
     */
    private function setMissingFields(array $params, &$user): array
    {
        $setKeys = [];
        foreach ($params as $key => $value) {
            if (!$user->$key) {
                $user->$key = $value;
                $setKeys[] = $key;
            } else {
                $message = __('strings.user.user_identifier_already_been_set', [
                    'key' => $key,
                ]);
                throw new \Exception($message, 400);
            }
        }
        return $setKeys;
    }
}
