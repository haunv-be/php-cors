<?php

namespace Enlightener\Cors;

use Enlightener\Cors\CorsService;

class CorsCollection
{
    /**
     * An array cors service instances registered keyed by origin.
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

        foreach ($origins as $origin) {
            $this->items[$origin] = $corsService;
        }
    }

    /**
     * Determine if the given key exists.
     */
    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    /**
     * Get the cors service instance with the given key.
     */
    public function get(string $key): ?CorsService
    {
        if ($this->has($key)) {
            return $this->items[$key];
        }
    }

    /**
     * Get cors service instance collection registered.
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
     * Get the last element in the collection.
     */
    public function last(): CorsService
    {
        return array_values($this->items())[$this->count() - 1];
    }
}