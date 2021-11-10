<?php

namespace App\Http\Controllers\Api\V1;

use App\Game;
use App\Gender;
use App\Http\Controllers\Controller;
use App\Http\Requests\SetIdentifiersRequest;
use App\Http\Requests\StoreUser;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Repositories\GameRepository;
use App\Repositories\TournamentRepository;
use App\Repositories\UserRepository;
use App\Tournament;
use App\Traits\Responses\ResponseMaker;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            if (array_key_exists('email', $setKeys)) {
                $this->dispatchUserJobs($user);
            }
            return $this->success(new UserResource($user));
        } catch (\Exception $exception) {
            return $this->failMessage($exception->getMessage(), $exception->getCode());
        }

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
            $userGames = $gameRepository->removeUserGame($user, $game->id);
            return $this->success($userGames);
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
        $pendingOrdersPoints = $user->orders()->where('is_final', false)->sum('redeem_points');
        $user->points = $user->points - $pendingOrdersPoints;
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
                throw new \Exception("This user has already set the $key field", 400);
            }
        }
        return $setKeys;
    }
}
