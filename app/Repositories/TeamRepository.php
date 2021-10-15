<?php

namespace App\Repositories;


use App\Game;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Link;
use App\LinkType;
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

    /**
     * @param UpdateTeamRequest $request
     * @param Team $team
     * @return Team
     */
    public function updateTeam(UpdateTeamRequest $request, Team $team)
    {
        $inputs = $request->validated();
        if ($request->hasFile('logo')) {
            $inputs['logo'] = $this->saveImageFromRequest($request, 'logo', 'teams/logos');
        }
        if ($request->hasFile('cover')) {
            $inputs['cover'] = $this->saveImageFromRequest($request, 'cover', 'teams/covers');
        }
        $links = [];
        if (array_key_exists('links', $inputs)) {
            $links = $inputs['links'];
            unset($inputs['links']);
        }
        foreach ($inputs as $key => $value) {
            $team->setAttribute($key, $value);
        }
        $team->save();
        $team->links()->delete();
        if ($links) {
            $this->saveTeamLinks($team, $links);
        }
        return $team->load(['links', 'links.linkType']);
    }

    /**
     * @param Team $team
     * @param array $links
     */
    private function saveTeamLinks(Team $team, array $links)
    {
        $linkTypes = LinkType::query()
            ->whereNotIn('title', ['Email', 'Website'])
            ->get()
            ->all();
        $emailType = LinkType::query()
            ->where('title', 'Email')
            ->first();
        $websiteType = LinkType::query()
            ->where('title', 'Website')
            ->first();
        foreach ($links as $value) {
            $link = strtolower($value);
            if (filter_var($link, FILTER_VALIDATE_EMAIL)) {
                if ($emailType) {
                    $model = new Link([
                        'url' => $link,
                        'linkable_type' => Team::class,
                        'linkable_id' => $team->id,
                        'link_type_id' => $emailType->id,
                    ]);
                    $model->save();
                }
                continue;
            } else if (filter_var($link, FILTER_VALIDATE_DOMAIN)) {
                $registered = false;
                foreach ($linkTypes as $linkType) {
                    $address = strtolower($linkType->title) . '.com';
                    if (strpos($link, $address) !== false) {
                        $model = new Link([
                            'url' => $link,
                            'linkable_type' => Team::class,
                            'linkable_id' => $team->id,
                            'link_type_id' => $linkType->id,
                        ]);
                        $model->save();
                        $registered = true;
                        break;
                    }
                }
                if (!$registered && $websiteType) {
                    $model = new Link([
                        'url' => $link,
                        'linkable_type' => Team::class,
                        'linkable_id' => $team->id,
                        'link_type_id' => $websiteType->id,
                    ]);
                    $model->save();
                }
            }

        }
    }
}
