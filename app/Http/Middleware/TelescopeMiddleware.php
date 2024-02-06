<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TelescopeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $ipWhitelist = env('IP_WHITELIST', '');
        $ipWhitelist = explode(',', $ipWhitelist);

        if (env('APP_ENV') !== 'local') {
            if (!in_array($request->ip(), $ipWhitelist)) {
                abort(403, 'Unauthorized action.');
            }
        }

        return $next($request);
    }
}
