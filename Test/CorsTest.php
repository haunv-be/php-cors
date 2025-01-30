<?php

namespace Enlightener\Test\Cors;

use Enlightener\Cors\Utils;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;
use Enlightener\Cors\HttpRequest;
use Enlightener\Cors\HttpResponse;
use Enlightener\Cors\Middleware\Cors;
use Enlightener\Cors\Exception\MethodNotAllowedException;

class CorsTest extends TestCase
{
    /**
     * Test the wildcard value with a preflight request.
     */
    public function testWildcardWithPreflightRequest(): void
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
     * Test values string with a preflight request.
     */
    public function testValuesStringWithPreflightRequest(): void
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
     * Test values array with a preflight request.
     */
    public function testValuesArrayWithPreflightRequest(): void
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
     * Test values array with a not allowed preflight request.
     */
    public function testValuesArrayWithNotAllowedPreflightRequest(): void
    {
        $this->expectException(MethodNotAllowedException::class);
        
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ORIGIN => 'https://example.com',
                        HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS => 'x-header-four,x-header-five',
                        HttpRequest::ACCESS_CONTROL_REQUEST_METHOD => 'PUT'
                    ]);

        $response = (new Cors([
                        'allowedOrigins' => ['https://php.net', 'https://laravel.com', 'https://symfony.com'],
                        'allowedMethods' => ['GET', 'HEAD', 'POST'],
                        'allowedHeaders' => ['X-Header-One', 'X-Header-Two', 'X-Header-Three'],
                    ]))
                    ->handle($request, function($request) {
                        return new Response;
                    });

        $this->assertNotEquals(
            $request->headers->get(HttpRequest::ORIGIN),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_ORIGIN)
        );

        $this->assertNotEquals(
            $request->headers->get(HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_HEADERS)
        );

        $this->assertNotEquals(
            $request->headers->get(HttpRequest::ACCESS_CONTROL_REQUEST_METHOD),
            $response->headers->get(HttpResponse::ACCESS_CONTROL_ALLOW_METHODS)
        );
    }

    /**
     * Test values array with an actual request.
     */
    public function testValuesArrayWithActualRequest(): void
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