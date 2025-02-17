<?php

namespace Enlightener\Test\Cors;

use Enlightener\Cors\Cors;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;
use Enlightener\Cors\HttpRequest;
use Enlightener\Cors\HttpResponse;
use Enlightener\Test\Cors\Browser;
use Enlightener\Cors\Exception\ForbiddenException;

class CorsManagerTest extends TestCase
{
    /**
     * Setup for each a test double.
     */
    public function setUp(): void
    {
        parent::__construct();

        Cors::collection()->flush();
    }

    /**
     * Test many cors service registered.
     */
    public function testManyCorsServiceRegistered(): void
    {
        Cors::origins('https://php.net, https://laravel.com, https://symfony.com')
                ->headers('X-Header-One, X-Header-Two, X-Header-Three')
                ->methods('GET, HEAD, POST')
                ->credentials(true)
                ->exposedHeaders('X-Header-One, X-Header-Two, X-Header-Three')
                ->maxAge(100);

        Cors::origins(['https://php.net', 'https://phpunit.de'])
                ->headers(['X-Header-One', 'X-Header-Two', 'X-Header-Three', 'X-Header-Four', 'X-Header-Five'])
                ->methods(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'])
                ->credentials(true)
                ->exposedHeaders(['X-Header-One', 'X-Header-Two', 'X-Header-Three', 'X-Header-Four', 'X-Header-Five'])
                ->maxAge(100);

        Cors::register([
            'origins' => 'https://test.com, https://example.com',
            'headers' => 'X-Header-One, X-Header-Two, X-Header-Three',
            'methods' => 'GET, HEAD, POST, PUT',
            'credentials' => true,
            'exposedHeaders' => 'X-Header-One, X-Header-Two, X-Header-Three',
            'maxAge' => 100
        ]);
                
        $this->assertSame(Cors::collection()->count(), 6);

        $this->assertSame(
            Cors::collection()->get('https://php.net')->allowedHeaders(),
            ['x-header-one', 'x-header-two', 'x-header-three', 'x-header-four', 'x-header-five']
        );

        $this->assertSame(
            Cors::collection()->get('https://test.com')->allowedMethods(),
            ['GET', 'HEAD', 'POST', 'PUT']
        );
    }

    /**
     * Test the "handle" method of cors dispatcher instance.
     */
    public function testHandleMethodOfCorsDispatcherInstance(): void
    {
        $request = (new Browser)
                    ->createRequest()
                    ->addHeaders([HttpRequest::ORIGIN => 'https://php.net'])
                    ->getRequest();

        Cors::origins('https://php.net');

        $response = Cors::handle($request, function() {
            return new Response;
        });

        $this->assertSame(
            'https://php.net', $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_ORIGIN)
        );
    }

    /**
     * Test the "handle" method of cors dispatcher instance with the wildcard value.
     */
    public function testHandleMethodOfCorsDispatcherInstanceWithWildcard(): void
    {
        $request = (new Browser)
                    ->createRequest()
                    ->addHeaders([HttpRequest::ORIGIN => 'https://api.example.com'])
                    ->getRequest();

        Cors::origins('*.example.com');

        $response = Cors::handle($request, function() {
            return new Response;
        });

        $this->assertSame(
            'https://api.example.com', $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_ORIGIN)
        );
    }
}