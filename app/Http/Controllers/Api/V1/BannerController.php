<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Team;
use App\Tournament;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use ResponseMaker;

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        $tournament = Tournament::first();
        $team = Team::first();

        $result = [
//            [
//                'id' => 1,
//                'type' => 'tournament',
//                'value' => $tournament->id,
//                'image' => $tournament->cover
//            ],
            [
                'id' => 2,
                'type' => 'team',
                'value' => $team->id,
                'image' => $team->cover
            ],
            [
                'id' => 3,
                'type' => 'web',
                'value' => 'https://google.com',
                'image' => Team::find(8)->cover
            ],
        ];

        return $this->success($result);
    }
}
