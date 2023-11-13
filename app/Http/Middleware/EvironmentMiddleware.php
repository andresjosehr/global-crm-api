<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ApiResponseController;
use Closure;
use Illuminate\Http\Request;

class EvironmentMiddleware
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
        // Check if the request is from the production environment
        // IP Whitelist
        $ipWhitelist = env('IP_WHITELIST', '');
        $ipWhitelist = explode(',', $ipWhitelist);

        if (env('APP_ENV') === 'production') {
            if (!in_array($request->ip(), $ipWhitelist)) {
                return ApiResponseController::response('Not found', 404);
            }
        }

        return $next($request);
    }
}
