<?php

namespace Enlightener\Cors\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Enlightener\Cors\CorsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class Cors
{
    /**
     * Default options to setting for the CORS service instance.
     *
     * @var array
     */
    protected $options = [
        'allowedOrigins' => ['*'],
        'allowedMethods' => ['*'],
        'allowedHeaders' => ['*'],
        'allowedCredentials' => false,
        'exposedHeaders' => [],
        'maxAge' => 0
    ];

    /**
     * Create a new CORS middleware instance.
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge(
            $this->options, $options
        );
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response|JsonResponse|RedirectResponse
    {
        $corsService = (new CorsService)
                        ->setRequest($request)
                        ->setAllowedOrigins($this->allowedOrigins)
                        ->setAllowedMethods($this->allowedMethods)
                        ->setAllowedHeaders($this->allowedHeaders)
                        ->setAllowedCredentials($this->allowedCredentials)
                        ->setExposedHeaders($this->exposedHeaders)
                        ->setMaxAge($this->maxAge);

        // We'll determine if the incoming request is the "preflight" request sent by the browser.
        // If exactly then, we'll check some information relevant to headers such as
        // "origin" and "access-control-request: headers, method".
        if ($corsService->isPreflightRequest()) {
            // If these header values are allowed on our server, then we return the response
            // to the browser with the "204" status code and headers force such as
            // "access-control-allow: origin, headers, methods" and optional "max-age" if any.
            // This work purpose is for the browser to know this request is allowed,
            // and it'll send the "actual" request to our server to handle.
            return $corsService->setResponse(new Response)
                               ->setStatusCode(Response::HTTP_NO_CONTENT)
                               ->configureAllowedOrigins()
                               ->configureAllowedHeaders()
                               ->configureAllowedMethods()
                               ->configureMaxAge()
                               ->getResponse();
        }

        // Handle the request
        $response = $next($request);

        $corsService->setResponse($response);
        // Here, we'll handle the "actual" request sent by the browser as mentioned above.
        // We'll apply the configurations such as "access-control-allow: origin, credentials"
        // and "access-control-exposed-headers" onto the "actual" response.
        // These are headers necessary and comply with the CORS policies.
        // After that, we'll send it to the browser side, and it has the responsibility
        // to handle our response to the client side.
        if ($corsService->isActualRequest()) {
            $corsService->configureAllowedOrigins()
                        ->configureAllowedCredentials()
                        ->configureExposedHeaders();
        }

        return $corsService->getResponse();
    }

    /**
     * Dynamically retrieve attributes on the "options" property.
     */
    public function __get(string $key): mixed
    {
        return $this->options[$key];
    }
}