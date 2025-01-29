<?php

namespace Enlightener\Cors;

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
     * Create a new cors manager instance.
     */
    public function __construct()
    {
        $this->collection = new CorsCollection;
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
     * Dynamically handle calls into the cors manager instance.
     */
    public function __call(string $method, array $parameters): mixed
    {
        if ($method == 'handle') {
            return (new CorsDispatcher)->handle(...$parameters);
        }
    }
}