<?php

namespace App\Http\Middleware;

use Closure;

class SetLocale
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
        $locale = strtolower($request->header('Language', 'en'));
        $availableLanguages = get_available_languages();
        if (in_array($locale, $availableLanguages)) {
            app()->setLocale($locale);
        }
        return $next($request);
    }
}
