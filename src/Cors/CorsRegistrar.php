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
     * The methods that can be set through this class.
     *
     * @var array
     */
    protected $methods = [
        'headers' => 'setAllowedHeaders',
        'methods' => 'setAllowedMethods',
        'credentials' => 'setAllowedCredentials',
        'exposedHeaders' => 'setExposedHeaders',
        'maxAge' => 'setMaxAge'
    ];

    /**
     * Create a new cors registrar instance.
     */
    public function __construct(CorsManager $corsManager)
    {
        $this->corsManager = $corsManager;
    }

    /**
     * Get the method name of the cors service instance with the given key.
     */
    protected function method(string $key): string
    {
        return $this->methods[$key];
    }

    /**
     * Determine if the method name exists with the given key.
     */
    protected function hasMethod(string $key): bool
    {
        return isset($this->methods[$key]);
    }

    /**
     * Get the current cors service instance.
     */
    protected function current(): CorsService
    {
        return $this->corsManager->collection()->last();
    }

    /**
     * Forward call to the cors service instance.
     */
    protected function forwardCallToCorsService(string $method, array|bool|string|int $value): self
    {
        $this->current()->{$this->method($method)}($value);

        return $this;
    }

    /**
     * Dynamically handled calls into the cors registrar instance.
     */
    public function __call(string $method, array $parameters): self
    {
        if ($this->hasMethod($method)) {
            return $this->forwardCallToCorsService($method, ...$parameters);
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}