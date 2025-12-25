<?php

namespace Irabbi360\LaravelApiInspector\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiInspectorMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('api-inspector.enabled', true)) {
            abort(404);
        }

        return $next($request);
    }
}
