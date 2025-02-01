<?php

namespace Enlightener\Test\Cors;

use Enlightener\Cors\Utils;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;
use Enlightener\Cors\HttpRequest;
use Enlightener\Cors\HttpResponse;
use Enlightener\Test\Cors\Browser;
use Enlightener\Cors\Middleware\Cors;
use Enlightener\Cors\Exception\HeaderNotAllowedException;
use Enlightener\Cors\Exception\MethodNotAllowedException;
use Enlightener\Cors\Exception\OriginNotAllowedException;

class CorsTest extends TestCase
{
    /**
     * Test with the wildcard value.
     */
    public function testWithWildcard(): void
    {
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ORIGIN => 'https://php.net',
                        HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS => 'x-header-one',
                        HttpRequest::ACCESS_CONTROL_REQUEST_METHOD => 'GET'
                    ]);

        $response = (new Cors)
                    ->handle($request, function($request) {
                        return new Response;
                    });

        $this->assertEquals(
            $request->headers->get(HttpRequest::ORIGIN),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_ORIGIN)
        );

        $this->assertEquals(
            $request->headers->get(HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_HEADERS)
        );

        $this->assertEquals(
            $request->headers->get(HttpRequest::ACCESS_CONTROL_REQUEST_METHOD),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_METHODS)
        );
    }

    /**
     * Test with the given values string.
     */
    public function testWithValuesString(): void
    {
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ORIGIN => 'https://php.net',
                        HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS => 'x-header-one',
                        HttpRequest::ACCESS_CONTROL_REQUEST_METHOD => 'GET'
                    ]);

        $response = (new Cors([
                        'allowedOrigins' => 'https://php.net, https://laravel.com, https://symfony.com',
                        'allowedMethods' => 'GET, HEAD, POST',
                        'allowedHeaders' => 'X-Header-One, X-Header-Two, X-Header-Three',
                        'maxAge' => 10
                    ]))
                    ->handle($request, function($request) {
                        return new Response;
                    });

        $this->assertEquals(
            $request->headers->get(HttpRequest::ORIGIN),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_ORIGIN)
        );

        $this->assertEquals(
            $request->headers->get(HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_HEADERS)
        );

        $this->assertEquals(
            $request->headers->get(HttpRequest::ACCESS_CONTROL_REQUEST_METHOD),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_METHODS)
        );

        $this->assertEquals(
            intval($response->headers->get(HttpResponse::ACCESS_CONTROL_MAX_AGE)),
            10
        );
    }

    /**
     * Test with the given values array.
     */
    public function testWithValuesArray(): void
    {
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ORIGIN => 'https://php.net',
                        HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS => 'x-header-one',
                        HttpRequest::ACCESS_CONTROL_REQUEST_METHOD => 'GET'
                    ]);

        $response = (new Cors([
                        'allowedOrigins' => ['https://php.net', 'https://laravel.com', 'https://symfony.com'],
                        'allowedMethods' => ['GET', 'HEAD', 'POST'],
                        'allowedHeaders' => ['X-Header-One', 'X-Header-Two', 'X-Header-Three'],
                        'maxAge' => 10
                    ]))
                    ->handle($request, function($request) {
                        return new Response;
                    });

        $this->assertEquals(
            $request->headers->get(HttpRequest::ORIGIN),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_ORIGIN)
        );

        $this->assertEquals(
            $request->headers->get(HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_HEADERS)
        );

        $this->assertEquals(
            $request->headers->get(HttpRequest::ACCESS_CONTROL_REQUEST_METHOD),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_METHODS)
        );

        $this->assertEquals(
            intval($response->headers->get(HttpResponse::ACCESS_CONTROL_MAX_AGE)),
            10
        );
    }

    /**
     * Test with the header values not allowed.
     */
    public function testWithHeadersNotAllowed(): void
    {
        $this->expectException(HeaderNotAllowedException::class);
        
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ORIGIN => 'https://example.com',
                        HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS => 'x-header-one,x-header-two',
                        HttpRequest::ACCESS_CONTROL_REQUEST_METHOD => 'GET'
                    ]);

        $response = (new Cors([
                        'allowedHeaders' => ['x-header-four', 'x-header-five', 'x-header-six']
                    ]))->handle($request, function($request) {
                        return new Response;
                    });

        $this->assertNotEquals(
            $request->headers->get(HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_HEADERS)
        );
    }

    /**
     * Test with the method value not allowed.
     */
    public function testWithMethodNotAllowed(): void
    {
        $this->expectException(MethodNotAllowedException::class);
        
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ORIGIN => 'https://example.com',
                        HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS => 'x-header-one,x-header-two',
                        HttpRequest::ACCESS_CONTROL_REQUEST_METHOD => 'PUT'
                    ]);

        $response = (new Cors(['allowedMethods' => ['GET', 'HEAD', 'POST']]))
                    ->handle($request, function($request) {
                        return new Response;
                    });

        $this->assertNotEquals(
            $request->headers->get(HttpRequest::ACCESS_CONTROL_REQUEST_METHOD),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_METHODS)
        );
    }

    /**
     * Test with the origin value not allowed.
     */
    public function testWithOriginNotAllowed(): void
    {
        $this->expectException(OriginNotAllowedException::class);
        
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ORIGIN => 'https://example.com',
                        HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS => 'x-header-one,x-header-two',
                        HttpRequest::ACCESS_CONTROL_REQUEST_METHOD => 'GET'
                    ]);

        $response = (new Cors([
                        'allowedOrigins' => ['https://php.net', 'https://laravel.com', 'https://symfony.com']
                    ]))->handle($request, function($request) {
                        return new Response;
                    });

        $this->assertNotEquals(
            $request->headers->get(HttpRequest::ORIGIN),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_ORIGIN)
        );
    }

    /**
     * Test with the actual request.
     */
    public function testCorsWithActualRequest(): void
    {
        $request = (new Browser)
                    ->createRequest()
                    ->addHeaders([HttpRequest::ORIGIN => 'https://php.net'])
                    ->getRequest();

        $response = (new Cors([
                        'allowedOrigins' => ['https://php.net', 'https://laravel.com', 'https://symfony.com'],
                        'allowedMethods' => ['GET', 'HEAD', 'POST'],
                        'allowedHeaders' => ['X-Header-One', 'X-Header-Two', 'X-Header-Three', 'X-Header-Four', 'X-Header-Five'],
                        'allowedCredentials' => true,
                        'exposedHeaders' => ['X-Header-One', 'X-Header-Two', 'X-Header-Three'],
                        'maxAge' => 10
                    ]))
                    ->handle($request, function($request) {
                        return new Response;
                    });

        $this->assertEquals(
            $request->headers->get(HttpRequest::ORIGIN),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_ORIGIN)
        );

        $this->assertEquals(
            boolval($response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_CREDENTIALS)),
            true
        );

        $this->assertTrue(Utils::strContains(
            $response->headers->get(HttpResponse::ACCESS_CONTROL_EXPOSE_HEADERS),
            'X-Header-One, X-Header-Two, X-Header-Three'
        ));
    }
}