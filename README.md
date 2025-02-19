## 🚀Features

* Configure options for each domain.
* Flexible about the usage way. 
* Handle the incoming requests strictly and write exceptions log if any to easy debug.

 ## About

<p dir="auto">
<a href="https://github.com/haunv-be/php-cors/actions"><img src="https://github.com/haunv-be/php-cors/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/enlightener/php-cors"><img src="https://img.shields.io/packagist/dt/enlightener/php-cors" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/enlightener/php-cors"><img src="https://img.shields.io/packagist/v/enlightener/php-cors" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/enlightener/php-cors"><img src="https://img.shields.io/packagist/l/enlightener/php-cors" alt="License"></a>
</p>

Enlightener PHP CORS is a small library support to prevent attacks from a cross-origin request. I spent a lot of time researching the mechanism, as well as how it works and summarized each line by comments. This library is for the `Laravel` framework and is also possible for the `Symfony` framework and the `PHP` language, but you must modify it if want to use it.
These documents I referenced and listed here:

* MDN Web Docs: [`CORS`](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)
* GitHub: [`asm89/stack-cors`](https://github.com/asm89/stack-cors)
* GitHub: [`fruitcake/laravel-cors`](https://github.com/fruitcake/laravel-cors)

> [!NOTE]
> Use for versions of `Laravel` from **6** to **10** or higher, and the `PHP` version must be from **7.4**

## Installation

```
composer require enlightener/php-cors
```

## Basic Usage

> [!NOTE]
> Attributes can be a string separation by comma or an array such as `foo, baz` | `['foo', 'baz']`

**Register a cors service**

```php
// Default options
Cors::origins('*');

// Register one service
Cors::origins('https://php.net')
        ->headers('X-Header-One, X-Header-Two, X-Header-Three')
        ->methods('GET, HEAD, POST')
        ->credentials(false)
        ->exposedHeaders('X-Header-One, X-Header-Two, X-Header-Three')
        ->maxAge(0);

// Register include wildcard in the domain
Cors::origin('*.example.com')
        ->headers('X-Header-One, X-Header-Two, X-Header-Three')
        ->methods('GET, HEAD, POST');

// Register many services
Cors::origins('https://php.net, https://laravel.com')
        ->headers('X-Header-One, X-Header-Two, X-Header-Three')
        ->methods('GET, HEAD, POST');

// or
Cors::origins(['https://php.net', 'https://laravel.com', '*.example.com'])
        ->headers(['X-Header-One', 'X-Header-Two', 'X-Header-Three'])
        ->methods(['GET', 'HEAD', 'POST'])
        ->exposedHeaders(['X-Header-One', 'X-Header-Two', 'X-Header-Three']);

// or
Cors::register([
    'origins' => ['https://php.net', 'https://laravel.com', '*.example.com'],
    'headers' => ['X-Header-One', 'X-Header-Two', 'X-Header-Three'],
    'credentials' => false,
    'exposedHeaders' => ['X-Header-One', 'X-Header-Two', 'X-Header-Three'],
    'maxAge' => 0
]);

// Retrieve all items in the collection
Cors::collection()->items();

// You can use any method that you want to meet the requirements of your project.
// Note that to register a cors service always start with the first "origins" method
// on each call a CORS facade instance.
```

**Handle a cors service**

```php
namespace App\Http\Middleware;

use Closure;
use Enlightener\Cors\Cors;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CorsHandler
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response|JsonResponse|RedirectResponse
    {
        Cors::origins(['https://php.net', 'https://laravel.com', 'https://symfony.com'])
                ->headers(['X-Header-One', 'X-Header-Two', 'X-Header-Three'])
                ->methods(['GET', 'HEAD', 'POST']);

        return Cors::handle($request, $next);
    }
}
```

**Configuration**

> [!NOTE]
> These options are strict, and this means that when you set an option that has the `[*]` value then it will be equivalent to the work you dynamically handled based on the incoming request. We will not disclose any values unnecessary for the browser side.

| Option            | Description                                                                  | Default value |
|-------------------|------------------------------------------------------------------------------|---------------|
| `origins`         | Origins are allowed so that the server side can share a resource.            | `[*]`         |
| `methods`         | Methods allowed when accessing a resource.                                   | `[*]`         |
| `headers`         | Headers that can be used during the actual request.                          | `[*]`         |
| `credentials`     | Credentials are allowed such as `cookies`, `tls`, `client certificates`, or `authentication headers`. | `false` |
| `exposedHeaders`  | Headers can be exposed to the browser side. | `[]` |
| `maxAge`          | The duration in seconds that the results of headers in a `preflight` request such as `access-control-allow: headers, methods` can cached. | `0` |

## License
The PHP CORS library is licensed under the [MIT license](https://github.com/haunv-be/php-cors/blob/main/LICENSE.md).