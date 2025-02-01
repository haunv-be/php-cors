<?php

namespace Enlightener\Cors;

use Illuminate\Http\Request;

class HttpRequest
{
    /**
     * Headers attached the "preflight" request sent by the browser side.
     */
    public const ORIGIN = 'Origin';
    public const ACCESS_CONTROL_REQUEST_HEADERS = 'Access-Control-Request-Headers';
    public const ACCESS_CONTROL_REQUEST_METHOD = 'Access-Control-Request-Method';

    /**
     * The request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * Create a new request decorator instance.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the request instance.
     */
    public function instance(): Request
    {
        return $this->request;
    }

    /**
     * Get the request header value with the given key.
     */
    public function header(string $key): ?string
    {
        return $this->request->headers->get($key);
    }

    /**
     * Determine if the request header exists.
     */
    public function hasHeader(string $key): bool
    {
        return $this->request->headers->has($key);
    }

    /**
     * Determine if the incoming request is sent by the "options" method.
     */
    protected function isOptionsMethod(): bool
    {
        return $this->request->method() === 'OPTIONS';
    }

    /**
     * Determine if the incoming request is a cross-origin request.
     */
    public function isCors(): bool
    {
        return $this->hasHeader(self::ORIGIN);
    }

    /**
     * Determine if the incoming request is a preflight request.
     */
    public function isPreflight(): bool
    {
        return $this->isOptionsMethod() &&
               $this->hasHeader(self::ORIGIN) &&
               $this->hasHeader(self::ACCESS_CONTROL_REQUEST_METHOD) &&
               $this->hasHeader(self::ACCESS_CONTROL_REQUEST_HEADERS);
    }

    /**
     * Get the "origin" header value from the preflight request or cross-origin request.
     */
    public function origin(): ?string
    {
        return $this->header(self::ORIGIN);
    }

    /**
     * Get the "access-control-request-headers" header value from the preflight request.
     */
    public function accessControlRequestHeaders(): ?string
    {
        return $this->header(
            self::ACCESS_CONTROL_REQUEST_HEADERS
        );
    }

    /**
     * Get the "assess-control-request-method" header value from the preflight request.
     */
    public function accessControlRequestMethod(): ?string
    {
        return $this->header(
            self::ACCESS_CONTROL_REQUEST_METHOD
        );
    }

    /**
     * Dynamically retrieve attributes on the request decorator instance.
     */
    public function __get(string $key): mixed
    {
        if ($key == 'headers') {
            return $this->request->{$key};
        }
    }

    /**
     * Dynamically handle calls into the request decorator instance.
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->request->{$method}(...$parameters);
    }
}