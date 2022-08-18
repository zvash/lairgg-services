<?php

namespace App\Http\Controllers\Api\V1;

use App\CoinTossReason;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCoinTossRequest;
use App\Http\Requests\CreateDisputeRequest;
use App\Lobby;
use App\Match;
use App\Repositories\LobbyRepository;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Http\Request;

class LobbyController extends Controller
{
    use ResponseMaker;

    /**
     * @param Request $request
     * @param string $lobbyName
     * @param LobbyRepository $lobbyRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getUserByLobbyName(Request $request, string $lobbyName, LobbyRepository $lobbyRepository)
    {
        $user = $request->user();
        $lobby = Lobby::where('name', $lobbyName)->first();
        if ($lobby && $lobbyRepository->userHasAccessToLobby($user, $lobby)) {
            $isOrganizer = $lobbyRepository->userIsAnOrganizerForLobby($user, $lobby);
            $team = $lobbyRepository->getUserTeamInLobby($user, $lobby);
            $user->is_organizer = $isOrganizer;
            $user->team = $team;
            return $this->success($user);
        }
        return $this->failNotFound();
    }

    public function sampleLobby(Request $request, string $token)
    {
        preg_match("/lobby_.*?\b/", $request->url(), $match);
        $params = [
            'token' => $token,
            'url' => config('lobby.url'),
            'room' => $match[0],
        ];
        return view('lobby.sample')->with(compact('params'));
    }

    /**
     * @param Request $request
     * @param string $lobbyName
     * @param LobbyRepository $lobbyRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function latest(Request $request, string $lobbyName, LobbyRepository $lobbyRepository)
    {
        $user = $request->user();
        $lobby = Lobby::where('name', $lobbyName)->first();
        if ($lobby && $lobbyRepository->userHasAccessToLobby($user, $lobby)) {
            return $this->success($lobbyRepository->getMessages($lobby));
        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @param string $lobbyName
     * @param string $uuid
     * @param LobbyRepository $lobbyRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function loadPrevious(Request $request, string $lobbyName, string $uuid, LobbyRepository $lobbyRepository)
    {
        $user = $request->user();
        $lobby = Lobby::where('name', $lobbyName)->first();
        if ($lobby&& $lobbyRepository->userHasAccessToLobby($user, $lobby)) {
            $messages = $lobbyRepository->loadPreviousMessages($lobby, $uuid);
            if ($messages !== null) {
                return $this->success($messages);
            }
        }
        return $this->failNotFound();
    }

    public function loadNext(Request $request, string $lobbyName, string $uuid, LobbyRepository $lobbyRepository)
    {
        $user = $request->user();
        $lobby = Lobby::where('name', $lobbyName)->first();
        if ($lobby&& $lobbyRepository->userHasAccessToLobby($user, $lobby)) {
            $messages = $lobbyRepository->loadNextMessages($lobby, $uuid);
            if ($messages !== null) {
                return $this->success($messages);
            }
        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @param string $lobbyName
     * @param LobbyRepository $lobbyRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function createCoinToss(CreateCoinTossRequest $request, string $lobbyName, LobbyRepository $lobbyRepository)
    {
        $user = $request->user();
        $lobby = Lobby::where('name', $lobbyName)->first();
        $inputs = $request->validated();
        if ($lobby/* && $lobbyRepository->issuerIsAParticipant($user, $lobby)*/) {
            return $this->success(['message_id' => $lobbyRepository->creatCoinTossMessage($user, $lobby, $inputs['title'])]);
        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @param string $lobbyName
     * @param string $uuid
     * @param LobbyRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function acceptCoinToss(Request $request, string $lobbyName, string $uuid, LobbyRepository $repository)
    {
        $user = $request->user();
        $lobby = Lobby::where('name', $lobbyName)->first();
        if ($lobby && $repository->issuerIsAParticipant($user, $lobby)) {
            $result = $repository->acceptCoinToss($user, $lobby, $uuid);
            if ($result !== null) {
                return $this->success(['has_won' => $result]);
            }
            return $this->failMessage(__('strings.invalid_request'), 400);
        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @param string $lobbyName
     * @param string $uuid
     * @param LobbyRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function declineCoinToss(Request $request, string $lobbyName, string $uuid, LobbyRepository $repository)
    {
        $user = $request->user();
        $lobby = Lobby::where('name', $lobbyName)->first();
        if ($lobby && $repository->issuerIsAParticipant($user, $lobby)) {
            $result = $repository->rejectCoinToss($user, $lobby, $uuid);
            if ($result !== null) {
                return $this->success($result);
            }
            return $this->failMessage(__('strings.invalid_request'), 400);
        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @param string $lobbyName
     * @param int $mapId
     * @param LobbyRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function pickMap(Request $request, string $lobbyName, int $mapId, LobbyRepository $repository)
    {
        $user = $request->user();
        $lobby = Lobby::where('name', $lobbyName)->first();
        if ($lobby && $repository->issuerIsAParticipant($user, $lobby)) {
            try {
                return $this->success(['message' => $repository->pickOrBanMap($user, $lobby, $mapId, 'pick')]);
            } catch (\Exception $exception) {
                return $this->failMessage($exception->getMessage(), 400);
            }
        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @param string $lobbyName
     * @param int $mapId
     * @param LobbyRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function banMap(Request $request, string $lobbyName, int $mapId, LobbyRepository $repository)
    {
        $user = $request->user();
        $lobby = Lobby::where('name', $lobbyName)->first();
        if ($lobby && $repository->issuerIsAParticipant($user, $lobby)) {
            try {
                return $this->success(['message' => $repository->pickOrBanMap($user, $lobby, $mapId, 'ban')]);
            } catch (\Exception $exception) {
                return $this->failMessage($exception->getMessage(), 400);
            }
        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @param string $lobbyName
     * @param int $mapId
     * @param string $mode
     * @param LobbyRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function selectSide(Request $request, string $lobbyName, int $mapId, string $mode, LobbyRepository $repository)
    {
        if (! in_array($mode, ['attacker', 'defender'])) {
            return $this->failMessage(__('strings.invalid_request'), 400);
        }
        $user = $request->user();
        $lobby = Lobby::where('name', $lobbyName)->first();
        if ($lobby && $repository->issuerIsAParticipant($user, $lobby)) {
            try {
                return $this->success(['message' => $repository->pickSide($user, $lobby, $mapId, $mode)]);
            } catch (\Exception $exception) {
                return $this->failMessage($exception->getMessage(), 400);
            }
        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @param string $lobbyName
     * @param LobbyRepository $lobbyRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function createDispute(CreateDisputeRequest $request, string $lobbyName, LobbyRepository $lobbyRepository)
    {
        $request->validated();
        $user = $request->user();
        $lobby = Lobby::where('name', $lobbyName)->first();
        if ($lobby && $lobby->owner instanceof Match && $lobbyRepository->issuerIsAParticipant($user, $lobby)) {
            return $this->success($lobbyRepository->createDisputeFromRequest($request, $lobby));
        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getCoinTossReasons(Request $request)
    {
        $reasons = CoinTossReason::query()->pluck('reason')->all();
        return $this->success($reasons);
    }

    public function getLobbyCoinTossReasons(Request $request, string $lobbyName)
    {
        $lobby = Lobby::where('name', $lobbyName)->first();
        if ($lobby && $lobby->owner instanceof Match) {
            $match = $lobby->owner;
            $gameId = $match->tournament->game->id;
            $reasons = CoinTossReason::query()->where('game_id', $gameId)->pluck('reason')->all();
            return $this->success($reasons);
        }
        return $this->failNotFound();
    }
}
