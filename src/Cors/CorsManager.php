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
        $this->addToCollections($origins);

        return $this->createCorsRegistrar();
    }

    /**
     * Register a cors service instance to the collection with the given options.
     */
    public function register(array $options = []): void
    {
        $origins = '*';

        if (isset($options['origins'])) {
            $origins = $options['origins'];
        }

        $this->addToCollections($origins);

        unset($options['origins']);

        $registrar = $this->createCorsRegistrar();

        if (! empty($options)) {
            foreach ($options as $method => $value) {
                $registrar->{$method}($value);
            }
        }
    }

    /**
     * Add a cors service instance to the collection with the given value.
     */
    protected function addToCollections(array|string $origins): void
    {
        $this->collection->add(
            $this->createCorsService($origins)
        );
    }

    /**
     * Create a new cors registrar instance.
     */
    protected function createCorsRegistrar(): CorsRegistrar
    {
        return new CorsRegistrar($this);
    }

    /**
     * Create a new cors service instance.
     */
    protected function createCorsService(array|string $origins): CorsService
    {
        return (new CorsService)
                ->setAllowedOrigins($origins)
                ->setAllowedHeaders('*')
                ->setAllowedMethods('*');
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