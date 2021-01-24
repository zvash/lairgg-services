<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Player;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    use ResponseMaker;

    /**
     * Get Player by Id
     *
     * @param Request $request
     * @param Player $player
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request, Player $player)
    {
        if (!$player) {
            return $this->failNotFound();
        }

        $detailedPlayer = $player->detailed->makeHidden('pivot');
        return $this->success($detailedPlayer);
    }
}
