<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Lobby;
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
            return $this->success($user);
        }
        return $this->failNotFound();
    }

    public function sampleLobby(Request $request, string $token)
    {
        $params = [
            'token' => $token,
            'url' => env('LOBBY_URL', 'http://lobby.dev.lair.gg:3000')
        ];
        return view('lobby.sample')->with(compact('params'));
    }
}
