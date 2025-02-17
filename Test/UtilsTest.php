<?php

namespace Enlightener\Test\Cors;

use Enlightener\Cors\Cors;
use Enlightener\Cors\Utils;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    /**
     * Test "arrayWrap" with the given arguments.
     */
    public function testArrayWrapWithArguments(): void
    {
        $values = Utils::arrayWrap('get, head, post');
        $this->assertSame(['get', 'head', 'post'], $values);

        $values = Utils::arrayWrap('get, head, post', 'strtoupper');
        $this->assertSame(['GET', 'HEAD', 'POST'], $values);

        $values = Utils::arrayWrap(['get', 'head', 'post']);
        $this->assertSame(['get', 'head', 'post'], $values);

        $values = Utils::arrayWrap(['get', 'head', 'post'], function($value) {
            return strtoupper($value);
        });
        $this->assertSame(['GET', 'HEAD', 'POST'], $values);
    }

    /**
     * Test "strContains" function with the given arguments.
     */
    public function testStringContainsWithArguments(): void
    {
        $this->assertTrue(Utils::strContains('get, head, post', 'head'));
        $this->assertTrue(Utils::strContains(['get', 'head', 'post'], 'head'));
        $this->assertFalse(Utils::strNotContains(['get', 'head', 'post'], 'head'));
    }

    /**
     * Test "match" function with the given arguments.
     */
    public function testMatchWithArguments(): void
    {
        Cors::origins('*.example.com');

        $items = Cors::collection()->items();

        $this->assertTrue(Utils::match($items, 'https://example.com', function($matched) {
            return $matched;
        }));

        $this->assertTrue(Utils::match($items, 'https://api.example.com', function($matched) {
            return $matched;
        }));

        $this->assertFalse(Utils::match($items, 'https://api.example.com.vn', function($matched) {
            return $matched;
        }));
    }
}