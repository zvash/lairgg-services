<?php

namespace App\Repositories;


use App\Game;
use App\User;

class GameRepository extends BaseRepository
{
    protected $modelClass = Game::class;

    /**
     * @param User $user
     * @param array $gameIds
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function syncUserGames(User $user, array $gameIds)
    {
        $pivotRows = [];
        foreach ($gameIds as $id) {
            $pivotRows[] = [
                'game_id' => $id,
                'username' =>$user->username,
            ];
        }
        $user->games()->sync($pivotRows);
        return $user->games()->get();
    }

    /**
     * @param User $user
     * @return array
     */
    public function getAllGamesAndUserSelectionStatus(User $user)
    {
        $selectedGames = $user->games()->get()->pluck('id')->toArray();
        $allGames = Game::all()->toArray();
        foreach ($allGames as $index => $game) {
            if (in_array($game['id'], $selectedGames)) {
                $allGames[$index]['selected_by_user'] = true;
            } else {
                $allGames[$index]['selected_by_user'] = false;
            }
        }
        return $allGames;
    }

}