<?php

namespace Enlightener\Cors;

use BadMethodCallException;
use Enlightener\Cors\CorsService;
use Enlightener\Cors\CorsRegistrar;
use Enlightener\Cors\CorsCollection;
use Enlightener\Cors\CorsDispatcher;

class CorsManager
{
    /**
     * The cors collection instance.
     *
     * @var CorsCollection
     */
    protected $collection;

    /**
     * The cors dispatcher instance.
     *
     * @var CorsDispatcher
     */
    protected $dispatcher;

    /**
     * Create a new cors manager instance.
     */
    public function __construct()
    {
        $this->collection = new CorsCollection;
        $this->dispatcher = new CorsDispatcher($this);
    }

    /**
     * Set "allowed origins" with the given value.
     */
    public function origins(array|string $origins): CorsRegistrar
    {    
        $this->collection->add(
            (new CorsService)
                ->setAllowedOrigins($origins)
                ->setAllowedHeaders('*')
                ->setAllowedMethods('*')
        );

        return new CorsRegistrar($this);
    }

    /**
     * Get the cors collection instance.
     */
    public function collection(): CorsCollection
    {
        return $this->collection;
    }

    /**
     * Get the cors dispatcher instance.
     */
    public function dispatcher(): CorsDispatcher
    {
        return $this->dispatcher;
    }

    /**
     * Dynamically handle calls into the cors manager instance.
     */
    public function __call(string $method, array $parameters): mixed
    {
        if ($method == 'handle') {
            return $this->dispatcher->handle(...$parameters);
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}