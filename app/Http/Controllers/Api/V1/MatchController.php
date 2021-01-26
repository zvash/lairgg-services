<?php

namespace App\Http\Controllers\Api\V1;

use App\Engines\BracketCreator;
use App\Match;
use App\Repositories\MatchRepository;
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
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function setPlayCount(Request $request, Match $match, MatchRepository $matchRepository)
    {
        if (!$match) {
            return $this->failNotFound();
        }

        $gate = Gate::inspect('setPlayCount', $match);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::UNAUTHORIZED);
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
            return $this->failMessage($gate->message(), HttpStatusCode::UNAUTHORIZED);
        }

        $disputes = $matchRepository->getDisputes($match);
        return $this->success($disputes);
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
