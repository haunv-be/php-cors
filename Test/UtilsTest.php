<?php

namespace Enlightener\Test\Cors;

use Enlightener\Cors\Utils;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    /**
     * Test utils function of array wrap with the given arguments.
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
     * Test utils function of string contains with the given arguments.
     */
    public function testStringContainsWithArguments(): void
    {
        $this->assertTrue(Utils::strContains('get, head, post', 'head'));
        $this->assertTrue(Utils::strContains(['get', 'head', 'post'], 'head'));
        $this->assertFalse(Utils::strNotContains(['get', 'head', 'post'], 'head'));
    }
}