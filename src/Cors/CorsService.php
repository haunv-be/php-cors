<?php

namespace Enlightener\Http;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CorsService
{
    /**
     * Wildcard value for requests without credentials.
     */
    public const WILDCARD = '*';

    /**
     * Headers attached the "preflight" request sent by the browser side.
     */
    public const ORIGIN = 'Origin';
    public const ACCESS_CONTROL_REQUEST_HEADERS = 'Access-Control-Request-Headers';
    public const ACCESS_CONTROL_REQUEST_METHOD = 'Access-Control-Request-Method';

    /**
     * Headers attached the "preflight" response sent by the server side
     * before receiving the actual request. Force these headers must sent 234.
     */
    public const VARY = 'Vary';
    public const ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    public const ACCESS_CONTROL_ALLOW_HEADERS = 'Access-Control-Allow-Headers';
    public const ACCESS_CONTROL_ALLOW_METHODS = 'Access-Control-Allow-Methods';
    public const ACCESS_CONTROL_ALLOW_CREDENTIALS = 'Access-Control-Allow-Credentials';
    public const ACCESS_CONTROL_EXPOSE_HEADERS = 'Access-Control-Expose-Headers';
    public const ACCESS_CONTROL_MAX_AGE = 'Access-Control-Max-Age';

    /**
     * The current request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * The current response instance.
     *
     * @var Response|JsonResponse|RedirectResponse
     */
    protected $response;

    /**
     * Headers are exposed for the browser side.
     *
     * @var string
     */
    protected $exposedHeaders;

    /**
     * Headers that can be used during the actual request.
     *
     * @var bool|string
     */
    protected $allowedHeaders;

    /**
     * Methods allowed when accessing a resource.
     *
     * @var bool|string
     */
    protected $allowedMethods;

    /**
     * Origins are allowed so that the server side can share a resource.
     *
     * @var array|bool
     */
    protected $allowedOrigins;

    /**
     * Credentials are allowed such as cookies, client certificates, and authentication headers.
     *
     * @var bool
     */
    protected $allowedCredentials = false;

    /**
     * The duration in seconds that the results of a "preflight" request such as
     * "Access-Control-Allow-Methods", "Access-Control-Allow-Headers" can cached.
     *
     * @var int
     */
    protected $maxAge = 0;

    /**
     * Get the current request instance.
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Set the current request instance.
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Determine if the incoming request is an "options" method.
     */
    public function isOptionsMethod(): bool
    {
        return $this->request->method() === 'OPTIONS';
    }

    /**
     * Determine if the incoming request is a cross-origin request.
     */
    protected function isCorsRequest(): bool
    {
        return $this->request->headers->has(self::ORIGIN);
    }

    /**
     * Determine if the incoming request is sent by the browser.
     */
    public function isPreflightRequest(): bool
    {
        return $this->isOptionsMethod() &&
               $this->request->headers->has(self::ORIGIN) &&
               $this->request->headers->has(self::ACCESS_CONTROL_REQUEST_METHOD) &&
               $this->request->headers->has(self::ACCESS_CONTROL_REQUEST_HEADERS);
    }

    /**
     * Get the "origin" header value from a cross-origin request.
     */
    protected function getRequestOrigin(): ?string
    {
        return $this->request->headers->get(self::ORIGIN);
    }

    /**
     * Get the "headers" header value from a cross-origin request.
     */
    protected function getRequestHeaders(): ?string
    {
        return $this->request->headers->get(
            self::ACCESS_CONTROL_REQUEST_HEADERS
        );
    }

    /**
     * Get the "method" header value from a cross-origin request.
     */
    protected function getRequestMethod(): ?string
    {
        return $this->request->headers->get(
            self::ACCESS_CONTROL_REQUEST_METHOD
        );
    }

    /**
     * Determine if the incoming request is the same host.
     */
    protected function isSameHost(): bool
    {
        return is_null($this->getRequestOrigin()) ||
               rtrim(env('APP_URL'), '/') === $this->request->getSchemeAndHttpHost();
    }

    /**
     * Get the response instance.
     */
    public function getResponse(): Response|JsonResponse|RedirectResponse
    {
        return $this->response;
    }

    /**
     * Set the response instance.
     */
    public function setResponse(Response|JsonResponse|RedirectResponse $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Determine if the allowed credentials value is supported.
     */
    protected function hasCredentials(): bool
    {
        return $this->allowedCredentials === true;
    }

    /**
     * Determine if the allowed credentials value is not supported.
     */
    protected function withoutCredentials(): bool
    {
        return ! $this->hasCredentials();
    }

    /**
     * Set the allowed credentials with the given value.
     */
    public function setAllowedCredentials(bool $value): self
    {
        $this->allowedCredentials = $value;

        return $this;
    }

    /**
     * Configure the allowed credentials value onto the response.
     */
    public function configureAllowedCredentials(): self
    {
        $this->response->headers->set(
            self::ACCESS_CONTROL_ALLOW_CREDENTIALS,
            $this->hasCredentials() ? 'true' : 'false'
        );

        return $this;
    }

    /**
     * Get the allowed headers value.
     */
    protected function allowedHeaders(): bool|string
    {
        return $this->allowedHeaders;
    }

    /**
     * Set the allowed headers with the given value.
     */
    public function setAllowedHeaders(array|string $headers): self
    {
        $headers = is_array($headers) ? $headers : [$headers];

        if (in_array(self::WILDCARD, $headers)) {
            $this->allowedHeaders = true;
        } else {
            $this->allowedHeaders = strtolower(implode(', ', $headers));
        }

        return $this;
    }

    /**
     * Configure the allowed headers value onto the response.
     */
    public function configureAllowedHeaders(): self
    { 
        // We'll determine if the allowed headers value is wildcard.
        // Here, we unset the wildcard value because some browsers are not supported fully
        // for this value if it attached with additional credentials.
        // Simultaneously, we want to narrow the scope of this value based on the incoming request
        // or more precisely is it like dynamically handled.
        // The purpose of this work is we'll cover all cases relevant to if has credentials or not.
        if ($this->allowedHeaders() === true) {
            $value = $this->getRequestHeaders();

            $this->setVaryHeader(
                self::ACCESS_CONTROL_REQUEST_HEADERS
            );
        } else {
            $value = $this->allowedHeaders();
        }

        $this->response->headers->set(
            self::ACCESS_CONTROL_ALLOW_HEADERS, $value
        );

        return $this;
    }

    /**
     * Get the allowed methods value.
     */
    protected function allowedMethods(): bool|string
    {
        return $this->allowedMethods;
    }

    /**
     * Set the allowed methods with the given value.
     */
    public function setAllowedMethods(array|string $methods): self
    {
        $methods = is_array($methods) ? $methods : [$methods];

        if (in_array(self::WILDCARD, $methods)) {
            $this->allowedMethods = true;
        } else {
            $this->allowedMethods = strtoupper(implode(', ', $methods));
        }

        return $this;
    }

    /**
     * Configure the allowed methods value onto the response.
     */
    public function configureAllowedMethods(): self
    {
        // For the same reason above, but here browsers are supported fully for this wildcard value.
        // But we still want to narrow the scope of this value to avoid exceptions if any.
        if ($this->allowedMethods() === true) {
            $value = $this->getRequestMethod();

            $this->setVaryHeader(
                self::ACCESS_CONTROL_REQUEST_METHOD
            );
        } else {
            $value = $this->allowedMethods();
        }

        $this->response->headers->set(
            self::ACCESS_CONTROL_ALLOW_METHODS, $value
        );

        return $this;
    }

    /**
     * Get the max age value.
     */
    protected function maxAge(): int
    {
        return $this->maxAge;
    }

    /**
     * Set the max age with the given seconds.
     */
    public function setMaxAge(int $seconds): self
    {
        $this->maxAge = $seconds;

        return $this;
    }

    /**
     * Configure the max age value onto the response.
     */
    public function configureMaxAge(): self
    {
        $this->response->headers->set(
            self::ACCESS_CONTROL_MAX_AGE, $this->maxAge()
        );

        return $this;
    }

    /**
     * Get the exposed headers value.
     */
    protected function exposedHeaders(): string|null
    {
        return $this->exposedHeaders;
    }

    /**
     * Set the exposed headers with the given value.
     */
    public function setExposedHeaders(array $headers): self
    {
        if (! in_array(self::WILDCARD, $headers) && ! empty($headers)) {
            $this->exposedHeaders = implode(', ', $headers);
        }

        return $this;
    }

    /**
     * Configure the exposed headers value onto the response.
     */
    public function configureExposedHeaders(): self
    {
        if (! is_null($this->exposedHeaders())) {
            $this->response->headers->set(
                self::ACCESS_CONTROL_EXPOSE_HEADERS, $value
            );
        }

        return $this;
    }

    /**
     * Determine if the response has the "vary" header value.
     */
    protected function hasVaryHeader(): bool
    {
        return $this->response->headers->has(self::VARY);
    }

    /**
     * Determine if the response is without the "vary" header value.
     */
    public function withoutVaryHeader(): bool
    {
        return ! $this->hasVaryHeader();
    }

    /**
     * Get the "vary" header value.
     */
    protected function varyHeader(): ?string
    {
        return $this->response->headers->get(self::VARY);
    }

    /**
     * Set the "vary" header with the given value.
     */
    public function setVaryHeader(string $header): self
    {
        if ($this->withoutVaryHeader()) {
            $this->response->headers->set(self::VARY, $header);
        } elseif (! str_contains($this->varyHeader(), $header)) {
            $this->response->headers->set(self::VARY, "{$this->varyHeader()}, {$header}");
        }

        return $this;
    }

    /**
     * Get the allowed origins value.
     */
    protected function allowedOrigins(): array|bool
    {
        return $this->allowedOrigins;
    }

    /**
     * Set the allowed origins with the given value.
     */
    public function setAllowedOrigins(array|string $origins): self
    {
        $origins = is_array($origins) ? $origins : [$origins];

        if (in_array(self::WILDCARD, $origins)) {
            $this->allowedOrigins = true;
        } else {
            $this->allowedOrigins = $origins;
        }

        return $this;
    }

    /**
     * Determine if allowed origins are the wildcard value.
     */
    protected function hasWildcardOrigin(): bool
    {
        return $this->allowedOrigins() === true;
    }

    /**
     * Determine if allowed origins are not the wildcard value.
     */
    protected function withoutWildcardOrigin(): bool
    {
        return ! $this->hasWildcardOrigin();
    }

    /**
     * Determine if allowed origins are the single value.
     */
    protected function isSingleOrigin(): bool
    {
        return $this->withoutWildcardOrigin() && count($this->allowedOrigins()) === 1;
    }

    /**
     * Determine if the "origin" header value from a cross-origin request is the allowed value.
     */
    protected function isOriginAllowed(): bool
    {
        return in_array($this->getRequestOrigin(), $this->allowedOrigins());
    }

    /**
     * Determine if a cross-origin request is allowed.
     */
    protected function isCorsRequestAllowed(): bool
    {
        return $this->isCorsRequest() && ($this->hasWildcardOrigin() || $this->isOriginAllowed());
    }

    /**
     * Determine if the incoming request is the actual request.
     */
    public function isActualRequest(): bool
    {
        return $this->isCorsRequestAllowed();
    }

    /**
     * Configure the allowed origins value onto the response.
     */
    public function configureAllowedOrigins(): self
    {
        if ($this->isSingleOrigin()) {
            $value = $this->allowedOrigins()[0];
        }

        // Here, we'll determine if the allowed origins value has many.
        // Imagine that you have an array that includes many values such as
        // ['https://laravel.com', 'https://github.com/haunv-be', ...] or the wildcard value mentioned above.
        // In this case, we must check the "Origin" header value from a cross-origin request
        // to determine that the value is exact with the given allowed values.
        elseif ($this->isCorsRequestAllowed()) {
            $value = $this->getRequestOrigin();

            // The "vary" header value has the purpose used to indicate to browsers that server
            // responses can differ based on the value of the "Origin" request header.
            // This header value is very useful to cache from the browser side.
            $this->setVaryHeader(self::ORIGIN);
        }

        $this->response->headers->set(
            self::ACCESS_CONTROL_ALLOW_ORIGIN, $value
        );

        return $this;
    }

    /**
     * Dynamically handle calls into the cors service.
     */
    public function __call(string $method, array $parameters): mixed
    {
        if ($method == 'setStatusCode') {
            $this->response->{$method}(...$parameters);
        }

        return $this;
    }
}