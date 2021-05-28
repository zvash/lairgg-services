<?php

namespace App\Http\Controllers\Api\V1;

use App\Game;
use App\Http\Controllers\Controller;
use App\Http\Requests\SetIdentifiersRequest;
use App\Http\Requests\StoreUser;
use App\Http\Resources\UserResource;
use App\Repositories\GameRepository;
use App\Repositories\TournamentRepository;
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
            'game_ids' => 'required|array|min:1',
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
