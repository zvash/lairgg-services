<?php

namespace App\Http\Controllers\Api\V1;

use App\Banner;
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
        $map = [
            'App\BannerUrl' => 'web',
            'App\Tournament' => 'tournament',
            'App\Team' => 'team',
        ];
        $banners = Banner::with('bannerable')->get();

        $results = [];
        foreach ($banners as $banner) {
            $results[] = [
                'id' => $banner->id,
                'type' => $map[$banner->bannerable_type],
                'value' => $map[$banner->bannerable_type] == 'web' ? $banner->bannerable->url : $banner->bannerable_id,
                'image' => $banner->image,
            ];
        }


        return $this->success($results);
    }
}
