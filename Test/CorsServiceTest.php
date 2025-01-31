<?php

namespace Enlightener\Test\Cors;

use Enlightener\Cors\Utils;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;
use Enlightener\Cors\CorsService;
use Enlightener\Cors\HttpRequest;
use Enlightener\Cors\HttpResponse;
use Enlightener\Test\Cors\Browser;
use Enlightener\Cors\Exception\HeaderNotAllowedException;
use Enlightener\Cors\Exception\MethodNotAllowedException;
use Enlightener\Cors\Exception\OriginNotAllowedException;

class CorsServiceTest extends TestCase
{
    /**
     * Test if the incoming request is a preflight request.
     */
    public function testIsPreflightRequest(): void
    {
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ORIGIN => 'https://example.com',
                        HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS => 'x-header-one',
                        HttpRequest::ACCESS_CONTROL_REQUEST_METHOD => 'GET'
                    ]);

        $corsService = (new CorsService)
                        ->setRequest($request);

        $this->assertTrue($corsService->isPreflightRequest());
    }

    /**
     * Test configure "allowed headers" with the wildcard value.
     */
    public function testConfigureAllowedHeadersWithWildcard(): void
    {
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS => 'x-header-one',
                    ]);

        $corsService = (new CorsService)
                        ->setRequest($request)
                        ->setResponse(new Response)
                        ->setAllowedHeaders('*')
                        ->configureAllowedHeaders();

        $this->assertTrue($corsService->allowedHeaders());

        $this->assertEquals(
            $corsService->request()->accessControlRequestHeaders(),
            $corsService->response()->accessControlAllowHeaders()
        );

        $this->assertEquals(
            HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS,
            $corsService->response()->varyHeader()
        );
    }

    /**
     * Test configure "allowed headers" with the given values string.
     */
    public function testConfigureAllowedHeadersWithValuesString(): void
    {
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS => 'x-header-one,x-header-two',
                    ]);

        $corsService = (new CorsService)
                        ->setRequest($request)
                        ->setResponse(new Response)
                        ->setAllowedHeaders('X-Header-One, X-Header-Two, X-Header-Three')
                        ->configureAllowedHeaders();
        
        $this->assertTrue(Utils::strContains(
            $corsService->allowedHeaders(), $corsService->request()->accessControlRequestHeaders()
        ));

        $this->assertEquals(
            $corsService->request()->accessControlRequestHeaders(),
            $corsService->response()->accessControlAllowHeaders()
        );

        $this->assertEquals(
            HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS,
            $corsService->response()->varyHeader()
        );
    }

    /**
     * Test configure "allowed headers" with the given values array.
     */
    public function testConfigureAllowedHeadersWithValuesArray(): void
    {
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS => 'x-header-one,x-header-two',
                    ]);

        $corsService = (new CorsService)
                        ->setRequest($request)
                        ->setResponse(new Response)
                        ->setAllowedHeaders(['X-Header-One', 'X-Header-Two', 'X-Header-Three'])
                        ->configureAllowedHeaders();
        
        $this->assertTrue(Utils::strContains(
            $corsService->allowedHeaders(), $corsService->request()->accessControlRequestHeaders()
        ));

        $this->assertEquals(
            $corsService->request()->accessControlRequestHeaders(),
            $corsService->response()->accessControlAllowHeaders()
        );

        $this->assertEquals(
            HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS,
            $corsService->response()->varyHeader()
        );
    }

    /**
     * Test configure "allowed headers" with the given values array with the request "headers" value not allowed.
     */
    public function testConfigureAllowedHeadersWithValuesArrayInquestHeadersNotAllowed(): void
    {
        $this->expectException(HeaderNotAllowedException::class);

        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ACCESS_CONTROL_REQUEST_HEADERS => 'x-header-one,x-header-two',
                    ]);

        $corsService = (new CorsService)
                        ->setRequest($request)
                        ->setResponse(new Response)
                        ->setAllowedHeaders(['X-Header-Four', 'X-Header-Five', 'X-Header-Six'])
                        ->configureAllowedHeaders();

        $this->assertNull($corsService->response()->varyHeader());
        $this->assertNull($corsService->response()->accessControlAllowHeaders());
        
        $this->assertFalse(Utils::strContains(
            $corsService->allowedHeaders(), $corsService->request()->accessControlRequestHeaders()
        ));
    }

    /**
     * Test configure "allowed methods" with the wildcard value.
     */
    public function testConfigureAllowedMethodsWithWildcard(): void
    {
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ACCESS_CONTROL_REQUEST_METHOD => 'GET',
                    ]);

        $corsService = (new CorsService)
                        ->setRequest($request)
                        ->setResponse(new Response)
                        ->setAllowedMethods('*')
                        ->configureAllowedMethods();

        $this->assertTrue($corsService->allowedMethods());

        $this->assertEquals(
            $corsService->request()->accessControlRequestMethod(),
            $corsService->response()->accessControlAllowMethods()
        );

        $this->assertEquals(
            HttpRequest::ACCESS_CONTROL_REQUEST_METHOD,
            $corsService->response()->varyHeader()
        );
    }

    /**
     * Test configure "allowed methods" with the given values string.
     */
    public function testConfigureAllowedMethodsWithValuesString(): void
    {
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ACCESS_CONTROL_REQUEST_METHOD => 'GET',
                    ]);

        $corsService = (new CorsService)
                        ->setRequest($request)
                        ->setResponse(new Response)
                        ->setAllowedMethods('GET, HEAD, POST')
                        ->configureAllowedMethods();

        $this->assertTrue(Utils::strContains(
            $corsService->allowedMethods(), $corsService->request()->accessControlRequestMethod()
        ));

        $this->assertEquals(
            $corsService->request()->accessControlRequestHeaders(),
            $corsService->response()->accessControlAllowHeaders()
        );

        $this->assertEquals(
            HttpRequest::ACCESS_CONTROL_REQUEST_METHOD,
            $corsService->response()->varyHeader()
        );
    }

    /**
     * Test configure "allowed methods" with the given values array.
     */
    public function testConfigureAllowedMethodsWithValuesArray(): void
    {
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ACCESS_CONTROL_REQUEST_METHOD => 'GET',
                    ]);

        $corsService = (new CorsService)
                        ->setRequest($request)
                        ->setResponse(new Response)
                        ->setAllowedMethods(['GET', 'HEAD', 'POST'])
                        ->configureAllowedMethods();
        
        $this->assertTrue(Utils::strContains(
            $corsService->allowedMethods(), $corsService->request()->accessControlRequestMethod()
        ));

        $this->assertEquals(
            $corsService->request()->accessControlRequestHeaders(),
            $corsService->response()->accessControlAllowHeaders()
        );

        $this->assertEquals(
            HttpRequest::ACCESS_CONTROL_REQUEST_METHOD,
            $corsService->response()->varyHeader()
        );
    }

    /**
     * Test configure "allowed methods" with the given values array and the request "method" value not allowed.
     */
    public function testConfigureAllowedMethodsWithValuesArrayInRequestMethodNotAllowed(): void
    {
        $this->expectException(MethodNotAllowedException::class);
        
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ACCESS_CONTROL_REQUEST_METHOD => 'PUT',
                    ]);

        $corsService = (new CorsService)
                        ->setRequest($request)
                        ->setResponse(new Response)
                        ->setAllowedMethods(['GET', 'HEAD', 'POST'])
                        ->configureAllowedMethods();

        $this->assertNull($corsService->response()->varyHeader());
        $this->assertNull($corsService->response()->accessControlAllowMethods());
        
        $this->assertFalse(Utils::strContains(
            $corsService->allowedMethods(), $corsService->request()->accessControlRequestMethod()
        ));
    }

    /**
     * Test configure "exposed headers" with the given values string.
     */
    public function testConfigureExposedHeadersWithValuesString(): void
    {
        $corsService = (new CorsService)
                        ->setResponse(new Response)
                        ->setExposedHeaders('X-Header-One, X-Header-Two')
                        ->configureExposedHeaders();

        $this->assertTrue(Utils::strContains(
            $corsService->response()->accessControlExposeHeaders(), 'X-Header-One, X-Header-Two'
        ));
    }

    /**
     * Test configure "exposed headers" with the given values array.
     */
    public function testConfigureExposedHeadersWithValuesArray(): void
    {
        $corsService = (new CorsService)
                        ->setResponse(new Response)
                        ->setExposedHeaders(['X-Header-One', 'X-Header-Two'])
                        ->configureExposedHeaders();
        
        $this->assertTrue(Utils::strContains(
            $corsService->response()->accessControlExposeHeaders(), 'X-Header-One, X-Header-Two'
        ));
    }

    /**
     * Test configure "max age" with the given value.
     */
    public function testConfigureMaxAgeValue(): void
    {
        $corsService = (new CorsService)
                        ->setResponse(new Response)
                        ->setMaxAge(10)
                        ->configureMaxAge();

        $this->assertIsString($corsService->response()->accessControlMaxAge());
    }

    /**
     * Test configure "max age" with the given zero value.
     */
    public function testConfigureMaxAgeZeroValue(): void
    {
        $corsService = (new CorsService)
                        ->setResponse(new Response)
                        ->setMaxAge(0)
                        ->configureMaxAge();

        $this->assertNull($corsService->response()->accessControlMaxAge());
    }

    /**
     * Test configure "allowed origins" with the given wildcard value.
     */
    public function testConfigureAllowedOriginsWithWildcard(): void
    {
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ORIGIN => 'https://php.net',
                    ]);

        $corsService = (new CorsService)
                        ->setRequest($request)
                        ->setResponse(new Response)
                        ->setAllowedOrigins('*')
                        ->configureAllowedOrigins();

        $this->assertTrue($corsService->allowedOrigins());

        $this->assertEquals(
            $corsService->request()->origin(),
            $corsService->response()->accessControlAllowOrigin()
        );

        $this->assertEquals(
            HttpRequest::ORIGIN,
            $corsService->response()->varyHeader()
        );
    }

    /**
     * Test configure "allowed origins" with the given values string.
     */
    public function testConfigureAllowedOriginsWithValuesString(): void
    {
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ORIGIN => 'https://php.net',
                    ]);

        $corsService = (new CorsService)
                        ->setRequest($request)
                        ->setResponse(new Response)
                        ->setAllowedOrigins('https://php.net, https://laravel.com, https://symfony.com')
                        ->configureAllowedOrigins();

        $this->assertTrue(Utils::strContains(
            $corsService->allowedOrigins(), $corsService->request()->origin()
        ));

        $this->assertEquals(
            $corsService->request()->origin(),
            $corsService->response()->accessControlAllowOrigin()
        );

        $this->assertEquals(
            HttpRequest::ORIGIN,
            $corsService->response()->varyHeader()
        );
    }

    /**
     * Test configure "allowed origins" with the given values array.
     */
    public function testConfigureAllowedOriginsWithValuesArray(): void
    {
        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ORIGIN => 'https://php.net',
                    ]);

        $corsService = (new CorsService)
                        ->setRequest($request)
                        ->setResponse(new Response)
                        ->setAllowedOrigins(['https://php.net', 'https://laravel.com', 'https://symfony.com'])
                        ->configureAllowedOrigins();

        $this->assertTrue(Utils::strContains(
            $corsService->allowedOrigins(), $corsService->request()->origin()
        ));

        $this->assertEquals(
            $corsService->request()->origin(),
            $corsService->response()->accessControlAllowOrigin()
        );

        $this->assertEquals(
            HttpRequest::ORIGIN,
            $corsService->response()->varyHeader()
        );
    }

    /**
     * Test configure "allowed origins" with the given values array and the request "origin" value not allowed.
     */
    public function testConfigureAllowedOriginsWithValuesArrayInRequestOriginNotAllowed(): void
    {
        $this->expectException(OriginNotAllowedException::class);

        $request = (new Browser)
                    ->createPreflightRequest([
                        HttpRequest::ORIGIN => 'https://example.com',
                    ]);

        $corsService = (new CorsService)
                        ->setRequest($request)
                        ->setResponse(new Response)
                        ->setAllowedOrigins(['https://php.net', 'https://laravel.com', 'https://symfony.com'])
                        ->configureAllowedOrigins();

        $this->assertNull($corsService->response()->varyHeader());
        $this->assertNull($corsService->response()->accessControlAllowOrigin());
        
        $this->assertFalse(Utils::strContains(
            $corsService->allowedOrigins(), $corsService->request()->origin()
        ));
    }
}