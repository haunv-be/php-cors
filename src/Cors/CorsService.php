<?php

namespace Enlightener\Cors;

use Enlightener\Cors\Utils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Enlightener\Cors\HttpRequest;
use Illuminate\Http\JsonResponse;
use Enlightener\Cors\HttpResponse;
use Illuminate\Http\RedirectResponse;
use Enlightener\Cors\Exception\MethodNotAllowedException;

class CorsService
{
    /**
     * Origins are allowed so that the server side can share a resource.
     *
     * @var array|bool
     */
    protected $allowedOrigins;
    
    /**
     * Headers that can be used during the actual request.
     *
     * @var array|bool
     */
    protected $allowedHeaders;

    /**
     * Methods allowed when accessing a resource.
     *
     * @var array|bool
     */
    protected $allowedMethods;

    /**
     * Credentials are allowed such as "cookies", "tls", "client certificates", or "authentication headers".
     *
     * @var bool
     */
    protected $allowedCredentials = false;

    /**
     * Headers can be exposed to the browser side.
     *
     * @var array
     */
    protected $exposedHeaders;

    /**
     * The duration in seconds that the results of headers in a "preflight" request
     * such as "access-control-allow: headers, methods" can cached.
     *
     * @var int
     */
    protected $maxAge = 0;

    /**
     * The request decorator instance.
     *
     * @var HttpRequest
     */
    protected $request;

    /**
     * The response decorator instance.
     *
     * @var HttpResponse
     */
    protected $response;

    /**
     * Get the request decorator instance.
     */
    public function request(): HttpRequest
    {
        return $this->request;
    }

    /**
     * Get the current request instance.
     */
    public function getRequest(): Request
    {
        return $this->request->instance();
    }

    /**
     * Create a new request decorator instance with the given current request.
     */
    public function setRequest(Request $request): self
    {
        $this->request = new HttpRequest($request);

        return $this;
    }

    /**
     * Get the response decorator instance.
     */
    public function response(): HttpResponse
    {
        return $this->response;
    }

    /**
     * Get the current response instance.
     */
    public function getResponse(): Response|JsonResponse|RedirectResponse
    {
        return $this->response->instance();
    }

    /**
     * Create a new response decorator instance with the given current response.
     */
    public function setResponse(Response|JsonResponse|RedirectResponse $response): self
    {
        $this->response = new HttpResponse($response);

        return $this;
    }

    /**
     * Determine if the "allowed credentials" value is supported.
     */
    protected function hasCredentials(): bool
    {
        return $this->allowedCredentials === true;
    }

    /**
     * Determine if the "allowed credentials" value is not supported.
     */
    protected function withoutCredentials(): bool
    {
        return ! $this->hasCredentials();
    }

    /**
     * Set "allowed credentials" with the given value.
     */
    public function setAllowedCredentials(bool $value): self
    {
        $this->allowedCredentials = $value;

        return $this;
    }

    /**
     * Configure the "allowed credentials" value onto the response.
     */
    public function configureAllowedCredentials(): self
    {
        $this->response->setAccessControlAllowCredentials(
            $this->hasCredentials() ? 'true' : 'false'
        );

        return $this;
    }

    /**
     * Get the "allowed headers" value.
     */
    public function allowedHeaders(): array|bool
    {
        return $this->allowedHeaders;
    }

    /**
     * Set "allowed headers" with the given value.
     */
    public function setAllowedHeaders(array|string $headers): self
    {
        $headers = Utils::arrayWrap($headers);

        $this->allowedHeaders = in_array('*', $headers) ? true : $headers;

        return $this;
    }

    /**
     * Determine if the "allowed headers" value is wildcard or contains
     * the request "access-control-request-headers" header value.
     */
    protected function hasAllowedHeaders(): bool
    {
        return $this->allowedHeaders() === true ||
               Utils::strContains($this->allowedHeaders(), $this->request->accessControlRequestHeaders());
    }

    /**
     * Configure the "allowed headers" value onto the response.
     */
    public function configureAllowedHeaders(): self
    { 
        // First, we'll determine if the "allowed headers" value is wildcard.
        // If exactly, we unset the wildcard value because some browsers are
        // not supported fully for this value if it is attached with additional credentials.
        // Simultaneously, we want to narrow the scope of this value based on
        // the incoming request or more precisely is it like dynamically handled.
        // The purpose of this work is we'll handle all cases relevant to credentials if has or not,
        // and security for headers unnecessarily to avoid showing to the browser side.
        if ($this->hasAllowedHeaders()) {
            $this->response->setVaryHeader(
                HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS
            );

            $this->response->setAccessControlAllowHeaders(
                $this->request->accessControlRequestHeaders()
            );
        }

        return $this;
    }

    /**
     * Get the "allowed methods" value.
     */
    public function allowedMethods(): array|bool
    {
        return $this->allowedMethods;
    }

    /**
     * Set "allowed methods" with the given value.
     */
    public function setAllowedMethods(array|string $methods): self
    {
        $methods = Utils::arrayWrap($methods);

        $this->allowedMethods = in_array('*', $methods) ? true : $methods;

        return $this;
    }

    /**
     * Determine if the "allowed methods" value is wildcard or contains
     * the request "access-control-request-method" header value.
     */
    protected function hasAllowedMethods(): bool
    {
        return $this->allowedMethods() === true ||
               Utils::strContains($this->allowedMethods(), $this->request->accessControlRequestMethod());
    }

    /**
     * Configure the "allowed methods" value onto the response.
     */
    public function configureAllowedMethods(): self
    {
        // Default, the browser side always allowed methods safe such as GET, HEAD, and POST.
        // This means that the server side does not need to set the "access-control-allow-methods"
        // header onto the preflight response. For example, you set allowed methods such as "PUT"
        // and "PATCH" on the server side. But if the request is sent by these methods listed above
        // then it'll still allowed. Some popular frameworks will prevent methods mismatch with routes.
        // Here we want strict in this problem and an exception will be thrown if any.
        if ($this->hasAllowedMethods()) {
            $this->response->setVaryHeader(
                HttpRequest::ACCESS_CONTROL_REQUEST_METHOD
            );

            $this->response->setAccessControlAllowMethods(
                $this->request->accessControlRequestMethod()
            );

            return $this;
        }

        throw new MethodNotAllowedException(
            "[{$this->request->accessControlRequestMethod()}] method not allowed."
        );
    }

    /**
     * Get the exposed headers value.
     */
    public function exposedHeaders(): ?array
    {
        return $this->exposedHeaders;
    }

    /**
     * Set "exposed headers" with the given value.
     */
    public function setExposedHeaders(array|string $headers): self
    {
        $headers = Utils::arrayWrap($headers);

        if (! in_array('*', $headers) && ! empty($headers)) {
            $this->exposedHeaders = $headers;
        }

        return $this;
    }

    /**
     * Configure the "exposed headers" value onto the response.
     */
    public function configureExposedHeaders(): self
    {
        // For example, you set headers such as "X-Header-One" and "X-Header-Two".
        // Then only these headers will be accessible in the browser side code.
        // Other headers, such as "X-Header-Four" or "X-Header-Five"
        // will not be exposed unless explicitly listed in this header.
        if (! is_null($value = $this->exposedHeaders())) {
            $this->response->setAccessControlExposeHeaders(
                implode(',', $value)
            );
        }

        return $this;
    }

    /**
     * Get the "max age" value.
     */
    public function maxAge(): int
    {
        return $this->maxAge;
    }

    /**
     * Set "max age" with the given seconds.
     */
    public function setMaxAge(int $seconds): self
    {
        $this->maxAge = $seconds;

        return $this;
    }

    /**
     * Configure the "max age" value onto the response.
     */
    public function configureMaxAge(): self
    {
        if ($this->maxAge() > 0) {
            $this->response->setAccessControlMaxAge($this->maxAge());
        }

        return $this;
    }

    /**
     * Get the "allowed origins" value.
     */
    public function allowedOrigins(): array|bool
    {
        return $this->allowedOrigins;
    }

    /**
     * Set "allowed origins" with the given value.
     */
    public function setAllowedOrigins(array|string $origins): self
    {
        $origins = Utils::arrayWrap($origins);

        $this->allowedOrigins = in_array('*', $origins) ? true : $origins;

        return $this;
    }

    /**
     * Determine if the "allowed origins" value is wildcard or contains
     * the request "access-control-request-origin" header value.
     */
    protected function hasAllowedOrigins(): bool
    {
        return $this->allowedOrigins() === true ||
               Utils::strContains($this->allowedOrigins(), $this->request->origin());
    }

    /**
     * Configure the "allowed origins" value onto the response.
     */
    public function configureAllowedOrigins(): self
    {
        if ($this->hasAllowedOrigins()) {
            $this->response->setVaryHeader(HttpRequest::ORIGIN);

            $this->response->setAccessControlAllowOrigin(
                $this->request->origin()
            );
        }

        return $this;
    }

    /**
     * Dynamically handle calls into the cors service.
     */
    public function __call(string $method, array $parameters): mixed
    {
        if ($method == 'setStatusCode') {
            $this->response->{$method}(...$parameters);
        } elseif ($method == 'isActualRequest') {
            return $this->request->isCors();
        } elseif ($method == 'isPreflightRequest') {
            return $this->request->isPreflight();
        }

        return $this;
    }
}