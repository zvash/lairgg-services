<?php

namespace App\Http\Middleware;

use App\Traits\Responses\ResponseMaker;
use Closure;
use Illuminate\Support\Facades\Auth;

class Verified
{
    use ResponseMaker;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($user && $user->email_verified_at) {
            return $next($request);
        }
        return $this->failData(['message' => 'User has not verified his email', 'code' => 1400], 403);
    }
}
