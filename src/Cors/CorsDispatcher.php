<?php

namespace Enlightener\Cors;

use Closure;
use Enlightener\Cors\Cors;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Enlightener\Cors\Middleware\Cors as Middleware;

class CorsDispatcher
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response|JsonResponse|RedirectResponse
    {
        $middleware = new Middleware;

        // First, we'll determine if the request "origin" header value
        // matches the "origin" value in the collection.
        // If exactly, we get the cors service instance in the collection and
        // setting it into middleware.
        if (! is_null($origin = $request->headers->get(HttpRequest::ORIGIN)) &&
            ! is_null($instance = Cors::collection()->get($origin))) {
            $middleware->setCorsService($instance);
        }
        
        // If the request "origin" header value no has on this request or
        // you have not yet set any values into the collection,
        // then the default options will be set into middleware.
        return $middleware->handle($request, $next);
    }
}