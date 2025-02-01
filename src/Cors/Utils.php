<?php

namespace Enlightener\Cors;

class Utils
{ 
    /**
     * Wrap to an array with the given value.
     */
    public static function arrayWrap(array|string|null $value, ?callable $callback = null): array
    {
        if (is_null($value)) {
            return [];
        }
        
        if (is_array($value)) {
            return array_map($callback, $value);
        }

        return static::arraySplit(',', $value, $callback);
    }

    /**
     * Split a string by a string.
     */
    public static function arraySplit(string $delimiter, string $string, ?callable $callback = null): array
    {
        $array = [];

        $string = explode($delimiter, $string);

        foreach ($string as $value) {
            $value = trim($value);

            if (empty($value)) {
                continue;
            }

            $array[] = is_callable($callback) ? $callback($value) : $value;
        }

        return $array;
    }

    /**
     * Determine if the given string contains the given value.
     */
    public static function strContains(array|string $string, string $value): bool
    {
        $string = static::arrayWrap($string);

        $value = static::arrayWrap($value);

        foreach ($value as $as) {
            if (! in_array($as, $string)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the given string does not contain the given value.
     */
    public static function strNotContains(array|string $string, string $value): bool
    {
        return ! static::strContains($string, $value);
    }

    /**
     * Get a random string with the given length.
     */
    public static function strRandom(int $length): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz';

        $string = '';

        for ($index = 0; $index < $length; $index++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $string;
    }
}