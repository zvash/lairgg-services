<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\GameRepository;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    use ResponseMaker;

    /**
     * Retrieves all available games
     *
     * @param GameRepository $gameRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function all(GameRepository $gameRepository)
    {
        $games = $gameRepository->all();
        return $this->success($games);
    }
}
