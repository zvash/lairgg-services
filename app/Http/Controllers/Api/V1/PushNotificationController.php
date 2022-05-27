<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\PushNotificationRepository;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    use ResponseMaker;

    /**
     * @param Request $request
     * @param PushNotificationRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function all(Request $request, PushNotificationRepository $repository)
    {
        return $this->success($repository->allForUser($request->user()));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $unreadCount = $user->pushNotifications()->whereNull('read_at')->count();
        return $this->success(['unread_count' => $unreadCount]);
    }
}
