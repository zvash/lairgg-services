<?php

namespace App\Http\Controllers\Api\V1;

use App\Engines\BracketCreator;
use App\Match;
use App\Repositories\LobbyRepository;
use App\Repositories\MatchRepository;
use App\Repositories\PlayRepository;
use Illuminate\Http\Request;
use App\Enums\HttpStatusCode;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Traits\Responses\ResponseMaker;
use App\Traits\Responses\ValidityChecker;

class MatchController extends Controller
{
    use ResponseMaker;
    use ValidityChecker;

    /**
     * Get match by id
     *
     * @param Request $request
     * @param Match $match
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request, Match $match)
    {
        if (!$match) {
            return $this->failNotFound();
        }

        return $this->success($match->load('plays', 'plays.parties'));
    }

    /**
     * @param Request $request
     * @param Match $match
     * @param MatchRepository $matchRepository
     * @param PlayRepository $playRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function forfeitAll(Request $request, Match $match, MatchRepository $matchRepository, PlayRepository $playRepository)
    {
        $user = $request->user();
        try {
            $participantId = $request->get('participant_id', 0);
            $matchRepository->forfeitAll($match, $user, $participantId, $playRepository);
            return response()->noContent();
        } catch (\Exception $exception) {
            return $this->failMessage($exception->getMessage(), 400);
        }
    }

    /**
     * @param Request $request
     * @param Match $match
     * @param MatchRepository $matchRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function setPlayCount(Request $request, Match $match, MatchRepository $matchRepository)
    {
        if (!$match) {
            return $this->failNotFound();
        }

        $gate = Gate::inspect('setPlayCount', $match);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::FORBIDDEN);
        }

        list($failed, $validator) = $this->validateSetGameCount($request);
        if ($failed) {
            return $this->failValidation($validator->errors());
        }

        $count = $request->get('count');
        $matchRepository->resetPlayCountForMatch($match, $count);
        $tournament = $match->tournament;
        $engine = new BracketCreator($tournament);
        $bracket = $engine->getBracket();
        return $this->success($bracket);
    }

    /**
     * @param Request $request
     * @param Match $match
     * @param MatchRepository $matchRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getDisputes(Request $request, Match $match, MatchRepository $matchRepository)
    {
        if (!$match) {
            return $this->failNotFound();
        }

        $gate = Gate::inspect('viewDisputes', $match);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::FORBIDDEN);
        }

        $disputes = $matchRepository->getDisputes($match);
        return $this->success($disputes);
    }

    /**
     * @param Request $request
     * @param Match $match
     * @param LobbyRepository $lobbyRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getLobbyName(Request $request, Match $match, LobbyRepository $lobbyRepository)
    {
        $lobby = $match->lobby;
        if ($lobby && $lobbyRepository->userHasAccessToLobby($request->user(), $lobby)) {
            return $this->success(['lobby_name' => $lobby->name]);
        }
        return $this->failNotFound();
    }

    /**
     * @param Request $request
     * @param Match $match
     * @param MatchRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function overview(Request $request, Match $match, MatchRepository $repository)
    {
        $user = $request->user();
        return $this->success($repository->specificMatchOverview($match, $user));
    }

    /**
     * @param Request $request
     * @param Match $match
     * @param MatchRepository $matchRepository
     * @param LobbyRepository $lobbyRepository
     * @param PlayRepository $playRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function setReady(Request $request, Match $match, MatchRepository $matchRepository, LobbyRepository $lobbyRepository, PlayRepository $playRepository)
    {
        $gate = Gate::inspect('setReady', $match);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::FORBIDDEN);
        }
        $user = $request->user();
        try {
            return $this->success($matchRepository->setReady($match, $user, $lobbyRepository, $playRepository));
        } catch (\Exception $exception) {
            return $this->failMessage($exception->getMessage(), HttpStatusCode::BAD_REQUEST);
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    private function validateSetGameCount(Request $request)
    {
        $rules = [
            'count' => 'required|int|min:1'
        ];
        return $this->validateRules($request, $rules);
    }
}
