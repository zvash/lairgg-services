<?php

namespace App\Http\Middleware;

use Closure;

class SetTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $offset = strtolower($request->header('Timezone', ''));
        $found = preg_match('/[-+]\d+:{0,1}\d*/', $offset, $matches);
        if ($matches && $found) {
            $offset = 0;
            $timeParts = explode(':', $matches[0]);
            $offset += $timeParts[0] * 60;
            if (array_key_exists(1, $timeParts)) {
                if ($offset < 0) {
                    $offset -= $timeParts[1] * 1;
                } else {
                    $offset += $timeParts[1] * 1;
                }
            }
            config(['app.request_utc_offset' => $offset]);
        }
        return $next($request);
    }
}
