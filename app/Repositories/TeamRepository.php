<?php

namespace App\Repositories;


use App\Game;
use App\Http\Requests\StoreTeamRequest;
use App\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamRepository extends BaseRepository
{
    public $modelClass = Team::class;

    /**
     * @param StoreTeamRequest $request
     * @return mixed
     */
    public function createTeamFromRequest(StoreTeamRequest $request)
    {
        $inputs = $request->validated();
        $inputs['logo'] = $this->saveImageFromRequest($request, 'logo', 'teams/logos');
        $inputs['cover'] = $this->saveImageFromRequest($request, 'cover', 'teams/covers');
        $team = Team::create($inputs);
        $team->players()->attach(Auth::user()->id, ['captain' => true]);
        return $team;
    }
}