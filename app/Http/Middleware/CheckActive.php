<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Responses\ErrorResponse;

class CheckActive
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
        if ($request->user() && $request->user()->active) {
            return $next($request);
        }

        return new ErrorResponse(401, 'Your account has been disabled.');
    }
}
