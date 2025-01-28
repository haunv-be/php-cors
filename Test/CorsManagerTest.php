<?php

namespace Enlightener\Test\Cors;

use Enlightener\Cors\Utils;
use PHPUnit\Framework\TestCase;
use Enlightener\Cors\CorsManager;

class CorsManagerTest extends TestCase
{
    /**
     * Test create multiple cors service.
     */
    public function testCreateMultipleCorsService(): void
    {
        $corsManager = new CorsManager;

        $corsManager->origins('https://php.net, https://laravel.com, https://symfony.com')
                    ->headers('X-Header-One, X-Header-Two, X-Header-Three')
                    ->methods('GET, HEAD, POST')
                    ->credentials(true)
                    ->exposedHeaders('X-Header-One, X-Header-Two, X-Header-Three')
                    ->maxAge(100);

        $corsManager->origins(['https://php.net', 'https://phpunit.de', 'https://example.com'])
                    ->headers(['X-Header-One', 'X-Header-Two', 'X-Header-Three', 'X-Header-Four', 'X-Header-Five'])
                    ->methods(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'])
                    ->credentials(true)
                    ->exposedHeaders(['X-Header-One', 'X-Header-Two', 'X-Header-Three', 'X-Header-Four', 'X-Header-Five'])
                    ->maxAge(100);

        $this->assertSame($corsManager->collection()->count(), 5);

        $this->assertSame(
            $corsManager->collection()->get('https://php.net')->allowedHeaders(),
            ['X-Header-One', 'X-Header-Two', 'X-Header-Three', 'X-Header-Four', 'X-Header-Five']
        );
    }
}