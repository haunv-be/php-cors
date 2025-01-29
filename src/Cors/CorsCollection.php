<?php

namespace Enlightener\Cors;

use Enlightener\Cors\CorsService;

class CorsCollection
{
    /**
     * An array of the cors services is keyed by origin.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Add a cors service instance to the collection.
     */
    public function add(CorsService $corsService): void
    {
        $origins = $corsService->allowedOrigins();

        $origins = is_array($origins) ? $origins : ['*'];

        foreach ($origins as $key) {
            $this->set($key, $corsService);
        }
    }

    /**
     * Determine if the given origin exists.
     */
    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    /**
     * Determine if the given origin does not exist.
     */
    public function without(string $key): bool
    {
        return ! $this->has($key);
    }

    /**
     * Get an item in the collection with the given origin.
     */
    public function get(string $key): ?CorsService
    {
        return $this->items[$key];
    }

    /**
     * Set an item onto the collection with the arguments.
     */
    public function set(string $key, CorsService $corsService): void
    {
        $this->unset($key);

        $this->items[$key] = $corsService;
    }

    /**
     * Get all items in the collection.
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Count the number of items in the collection.
     */
    public function count(): int
    {
        return count($this->items());
    }

    /**
     * Remove an item in the collection with the given origin.
     */
    public function unset(string $key): void
    {
        if ($this->has($key)) {
            unset($this->items[$key]);
        }
    }

    /**
     * Get the last element in the collection.
     */
    public function last(): CorsService
    {
        return array_values($this->items())[$this->count() - 1];
    }

    /**
     * Determine if the collection is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->items());
    }

    /**
     * Determine if the collection is not empty.
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Flush all items in the collection.
     */
    public function flush(): void
    {
        $this->items = [];
    }
}