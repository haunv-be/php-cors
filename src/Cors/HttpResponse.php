<?php

namespace Enlightener\Cors;

use Enlightener\Cors\Utils;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class HttpResponse
{
    /**
     * Headers attached the "preflight" response sent by the server side
     * before receiving the "actual" request. These headers force sent such as
     * "allow: origin, headers, methods" and optional "max-age" if any.
     */
    public const VARY = 'Vary';
    public const ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    public const ACCESS_CONTROL_ALLOW_HEADERS = 'Access-Control-Allow-Headers';
    public const ACCESS_CONTROL_ALLOW_METHODS = 'Access-Control-Allow-Methods';
    public const ACCESS_CONTROL_ALLOW_CREDENTIALS = 'Access-Control-Allow-Credentials';
    public const ACCESS_CONTROL_EXPOSE_HEADERS = 'Access-Control-Expose-Headers';
    public const ACCESS_CONTROL_MAX_AGE = 'Access-Control-Max-Age';

    /**
     * The response instance.
     *
     * @var Response|JsonResponse|RedirectResponse
     */
    protected $response;

    /**
     * Create a new response decorator instance.
     */
    public function __construct(Response|JsonResponse|RedirectResponse $response)
    {
        $this->response = $response;
    }

    /**
     * Get the response instance.
     */
    public function instance(): Response|JsonResponse|RedirectResponse
    {
        return $this->response;
    }

    /**
     * Get the response header value with the given key.
     */
    public function header(string $key): ?string
    {
        return $this->response->headers->get($key);
    }

    /**
     * Set the response header value with the given arguments.
     */
    public function setHeader(string $key, string|array|null $values): void
    {
        $this->response->headers->set($key, $values);
    }

    /**
     * Determine if the response header exists.
     */
    public function hasHeader(string $key): bool
    {
        return $this->response->headers->has($key);
    }

    /**
     * Determine if the response has the "vary" header.
     */
    protected function hasVaryHeader(): bool
    {
        return $this->hasHeader(self::VARY);
    }

    /**
     * Determine if the response is without the "vary" header.
     */
    protected function withoutVaryHeader(): bool
    {
        return ! $this->hasVaryHeader();
    }

    /**
     * Get the response "vary" header value.
     */
    protected function varyHeader(): ?string
    {
        return $this->header(self::VARY);
    }

    /**
     * Set the response "vary" header with the given value.
     */
    public function setVaryHeader(string $value): void
    {
        if ($this->withoutVaryHeader()) {
            $this->setHeader(self::VARY, $value);
        } elseif (Utils::strNotContains($this->varyHeader(), $value)) {
            $this->setHeader(self::VARY, "{$this->varyHeader()}, {$value}");
        }
    }

    /**
     * Set the response "access-control-allow-origin" header with the given value.
     */
    public function setAccessControlAllowOrigin(string $value): void
    {
        $this->setHeader(
            self::ACCESS_CONTROL_ALLOW_ORIGIN, $value
        );
    }

    /**
     * Set the response "access-control-allow-headers" header with the given value.
     */
    public function setAccessControlAllowHeaders(string $value): void
    {
        $this->setHeader(
            self::ACCESS_CONTROL_ALLOW_HEADERS, $value
        );
    }

    /**
     * Set the response "access-control-allow-methods" header with the given value.
     */
    public function setAccessControlAllowMethods(string $value): void
    {
        $this->setHeader(
            self::ACCESS_CONTROL_ALLOW_METHODS, $value
        );
    }

    /**
     * Set the response "access-control-allow-credentials" header with the given value.
     */
    public function setAccessControlAllowCredentials(string $value): void
    {
        $this->setHeader(
            self::ACCESS_CONTROL_ALLOW_CREDENTIALS, $value
        );
    }

    /**
     * Set the response "access-control-expose-headers" header with the given value.
     */
    public function setAccessControlExposeHeaders(string $value): void
    {
        $this->setHeader(
            self::ACCESS_CONTROL_EXPOSE_HEADERS, $value
        );
    }

    /**
     * Set the response "access-control-max-age" header with the given value.
     */
    public function setAccessControlMaxAge(int $value): void
    {
        $this->setHeader(
            self::ACCESS_CONTROL_MAX_AGE, $value
        );
    }

    /**
     * Dynamically retrieve attributes on the response decorator instance.
     */
    public function __get(string $key): mixed
    {
        if ($key === 'headers') {
            return $this->response->{$key};
        }
    }

    /**
     * Dynamically handle calls into the response decorator instance.
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->response->{$method}(...$parameters);
    }
}