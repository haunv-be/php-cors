<?php

namespace Enlightener\Cors;

use Enlightener\Cors\CorsManager;

class Cors
{
    /**
     * The singleton cors manager instance.
     *
     * @var CorsManager
     */
    protected static $instance;

    /**
     * Get the singleton cors manager instance.
     */
    public static function instance(): CorsManager
    {
        if (is_null(static::$instance)) {
            static::$instance = new CorsManager;
        }

        return static::$instance;
    }

    /**
     * Dynamically handled static calls to the object.
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        return static::instance()->{$method}(...$parameters);
    }
}