<?php

namespace Enlightener\Cors;

use BadMethodCallException;
use Enlightener\Cors\CorsManager;
use Enlightener\Cors\CorsService;

class CorsRegistrar
{
    /**
     * The cors manager instance.
     *
     * @var CorsManager
     */
    protected $corsManager;

    /**
     * Create a new cors registrar instance.
     */
    public function __construct(CorsManager $corsManager)
    {
        $this->corsManager = $corsManager;
    }

    /**
     * Get the current cors service instance.
     */
    protected function current(): CorsService
    {
        return $this->corsManager->collection()->last();
    }

    /**
     * Set "allowed credentials" with the given value.
     */
    public function credentials(bool $value): self
    {
        $this->current()->setAllowedCredentials($value);

        return $this;
    }

    /**
     * Set "allowed headers" with the given value.
     */
    public function headers(array|string $headers): self
    {
        $this->current()->setAllowedHeaders($headers);

        return $this;
    }

    /**
     * Set "allowed methods" with the given value.
     */
    public function methods(array|string $methods): self
    {
        $this->current()->setAllowedMethods($methods);

        return $this;
    }

    /**
     * Set "exposed headers" with the given value.
     */
    public function exposedHeaders(array|string $headers): self
    {
        $this->current()->setExposedHeaders($headers);

        return $this;
    }

    /**
     * Set "max age" with the given seconds.
     */
    public function maxAge(int $seconds): self
    {
        $this->current()->setMaxAge($seconds);

        return $this;
    }
}