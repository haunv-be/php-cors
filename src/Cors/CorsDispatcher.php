<?php

namespace Enlightener\Cors;

use Closure;
use Enlightener\Cors\Utils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Enlightener\Cors\CorsManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Enlightener\Cors\Middleware\Cors as Middleware;

class CorsDispatcher
{
    /**
     * The cors manager instance.
     *
     * @var CorsManager
     */
    protected $manager;

    /**
     * Create a new cors dispatcher instance.
     */
    public function __construct(CorsManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response|JsonResponse|RedirectResponse
    {
        $middleware = new Middleware;

        $collection = $this->manager->collection();

        // If the incoming request is sent by the same host, or collection is empty
        // then we'll create a new cors service with the default options.
        if ($this->isSameHost($request) || $collection->isEmpty()) {
            $instance = $this->manager->origins('*')->current();
        }

        // If the collection exists the wildcard value then
        // we get the cors service instance of that value
        // and ignore all remaining values if any.
        elseif ($collection->has('*')) {
            $instance = $collection->get('*');
        }

        // If the incoming request exists the "origin" header value
        // and matches the "origin" value in the collection.
        // Then we get the cors service instance of this value.
        elseif (! is_null($origin = $request->headers->get(HttpRequest::ORIGIN)) &&
                $collection->has($origin)) {
            $instance = $collection->get($origin);
        }

        // If the incoming request does not pass any of the conditions above
        // then this request is not allowed from our server. We'll create a new cors service
        // with the custom origin value to automatically throw exceptions in configuration steps.
        else {
            $instance = $this->manager->origins(
                'https://'.Utils::strRandom(10).'.com'
            )->current();
        }
        
        return $middleware->setCorsService($instance)->handle($request, $next);
    }

    /**
     * Determine if the incoming request is the same host.
     */
    protected function isSameHost(Request $request): bool
    {
        $origin = $request->headers->get(HttpRequest::ORIGIN);

        $schemeAndHttpHost = $request->getSchemeAndHttpHost();

        return is_null($origin) ||
               $origin === $schemeAndHttpHost;
               $origin === rtrim(env('APP_URL') ?? '', '/');
    }

    /**
     * Determine if the incoming request is not the same host.
     */
    protected function isNotSameHost(Request $request): bool
    {
        return ! $this->isSameHost($request);
    }
}