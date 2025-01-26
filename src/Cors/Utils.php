<?php

namespace Enlightener\Cors;

class Utils
{ 
    /**
     * Wrap to an array with the given value.
     */
    public static function arrayWrap(array|string|null $value): array
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : static::arraySplit(',', $value);
    }

    /**
     * Split a string by a string.
     */
    public static function arraySplit(string $delimiter, string $string): array
    {
        $array = [];

        $strings = explode($delimiter, $string);

        foreach ($strings as $value) {
            $value = trim($value);

            if (empty($value)) {
                continue;
            }

            $array[] = $value;
        }

        return $array;
    }

    /**
     * Determine if the given string contains the given value.
     */
    public static function strContains(string $string, array|string $value): bool
    {
        $strings = static::arrayWrap($string);

        $values = static::arrayWrap($value);

        foreach ($values as $as) {
            if (! in_array($as, $strings)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the given string does not contain the given value.
     */
    public static function strNotContains(string $string, array|string $value): bool
    {
        return ! static::strContains($string, $value);
    }
}