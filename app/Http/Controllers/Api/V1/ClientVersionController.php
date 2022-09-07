<?php

namespace App\Http\Controllers\Api\V1;

use App\ClientVersion;
use App\Http\Controllers\Controller;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Http\Request;

class ClientVersionController extends Controller
{
    use ResponseMaker;

    /**
     * @param Request $request
     * @param string $type
     * @param int $version
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updateStatus(Request $request, string $type, string $version)
    {
        if (!is_numeric($version)) {
            return $this->failMessage('Version must be a numeric value', 400);
        }
        $version *= 1;
        $latestClientVersion = ClientVersion::query()
            ->where('client_type', $type)
            ->latest('version')
            ->first();
        if (!$latestClientVersion || $latestClientVersion->version < $version) {
            return $this->success([
                'need_update' => false,
                'is_force' => false,
                'latest_version_code' => 'N/A',
            ]);
        }
        if ($latestClientVersion->version == $version) {
            return $this->success([
                'need_update' => false,
                'is_force' => false,
                'latest_version_code' => $latestClientVersion->code,
            ]);
        }
        $isForceUpdate = ClientVersion::query()
            ->where('client_type', $type)
            ->where('version', '>', $version)
            ->where('is_forced', true)
            ->count() > 0;
        return $this->success([
            'need_update' => true,
            'is_force' => $isForceUpdate,
            'latest_version_code' => $latestClientVersion->code,
        ]);
    }
}
