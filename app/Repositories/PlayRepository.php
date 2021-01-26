<?php

namespace App\Repositories;


use App\Play;
use App\User;
use Illuminate\Http\Request;


class PlayRepository extends BaseRepository
{
    protected $modelClass = Play::class;

    /**
     * Updates an existing organization
     *
     * @param Request $request
     * @param Play $play
     * @param User $user
     * @return Play
     */
    public function editPlayWithRequest(Request $request, Play $play, User $user)
    {
        $inputs = array_filter($request->all(), function ($key) {
            return in_array($key, ['map_id']);
        }, ARRAY_FILTER_USE_KEY);
        $screenshot = $this->saveImageFromRequest($request, 'screenshot', 'plays/screenshots');
        if ($screenshot) {
            $inputs['screenshot'] = $screenshot;
        }
        $inputs['edited_by'] = $user->id;
        foreach ($inputs as $key => $value) {
            $play->setAttribute($key, $value);
        }
        $play->save();
        return $play;
    }
}