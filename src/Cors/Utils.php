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

        $string = explode($delimiter, $string);

        foreach ($string as $value) {
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
}