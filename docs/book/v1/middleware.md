# Middleware

mezzio-cors provides middleware consuming
[PSR-7](http://www.php-fig.org/psr/psr-7/) HTTP message instances, via
implementation of [PSR-15](https://www.php-fig.org/psr/psr-15/) interfaces.

This middleware checks, if the incoming request is a CORS request. If so, it
makes a distinction between a so called [Preflight request](#preflight-request)
or the [regular request](#cors-request).

## Preflight Request

A Preflight request should be a light call which provides the Browser with the
CORS informations it needs to execute the regular (CORS) request.

These informations are:

- Domain accepted for executing CORS request?
- Cookies accepted?
- Which Headers are allowed to be sent?
- Which Headers are provided in the response?
- Which HTTP Methods are accepted by that Endpoint?

## CORS Request

The CORS request is the actual request. That request SHOULD to be already
verified. If its not verified by a previous [Preflight request](#preflight-request),
the request will be aborted with a `403 Forbidden` response.

## Configuration

There are 2 ways of configuring CORS in your project. Either create a global
configuration file like `cors.global.php` and/or add a route specific configuration.

On the project level, you can only configure the following Headers:

| Configuration | Type | Header
|:-------------|:-------------:|:-----:
| `allowed_origins` | string[] | Access-Control-Allow-Origin
| `allowed_headers` | string[] | Access-Control-Allow-Headers
| `allowed_max_age` | string (TTL in seconds) | Access-Control-Max-Age
| `credentials_allowed` | bool | Access-Control-Allow-Credentials
| `exposed_headers` | string[] | Access-Control-Expose-Headers

> The `allowed_origins` strings must fit the [`fnmatch`](https://www.php.net/manual/en/function.fnmatch.php) format.**

On the route level, you can configure all of the projects configuration settings
and if the configuration of the route should either override the project
configuration (default) or merge it.

| Configuration | Type | Header
|:------------- |:-------------:|:-----:
| `overrides_project_configuration` | bool | -
| `explicit` | bool | -
| `allowed_origins` | string[] | Access-Control-Allow-Origin
| `allowed_headers` | string[] | Access-Control-Allow-Headers
| `allowed_max_age` | string (TTL in seconds) | Access-Control-Max-Age
| `credentials_allowed` | bool | Access-Control-Allow-Credentials
| `exposed_headers` | string[] | Access-Control-Expose-Headers

The parameter `overrides_project_configuration` handles the way how the
configuration is being merged. The default setting is `true` to ensure that a
route configuration has to specify every information it will provide.

The parameter `explicit` tells the `ConfigurationLocator` to stop trying other
request methods to match the same route because there wont be any other method.

### Examples for Project Configurations

#### Allow Every Origin

```php
<?php
// In config/autoload/cors.global.php
declare(strict_types=1);

use Mezzio\Cors\Configuration\ConfigurationInterface;

return [
    ConfigurationInterface::CONFIGURATION_IDENTIFIER => [
        'allowed_origins' => [ConfigurationInterface::ANY_ORIGIN], // Allow any origin
        'allowed_headers' => [], // No custom headers allowed
        'allowed_max_age' => '600', // 10 minutes
        'credentials_allowed' => true, // Allow cookies
        'exposed_headers' => ['X-Custom-Header'], // Tell client that the API will always return this header  
    ],
];
```

#### Allow Every Origin from a Specific Domain and Its Subdomains

```php
<?php
// In config/autoload/cors.global.php

declare(strict_types=1);

use Mezzio\Cors\Configuration\ConfigurationInterface;

return [
    ConfigurationInterface::CONFIGURATION_IDENTIFIER => [
        'allowed_origins' => ['*//example.com', '*.example.com'],
        'allowed_headers' => [], // No custom headers allowed
        'allowed_max_age' => '3600', // 60 minutes
        'credentials_allowed' => false, // Disallow cookies
        'exposed_headers' => [], // No headers are exposed  
    ],
];
```

### Examples for Route Configurations

#### Make the Configuration Explicit to Avoid Multiple Router Match Requests

```php
<?php
// In config/autoload/cors.global.php
declare(strict_types=1);

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\RouteConfigurationInterface;

return [
    ConfigurationInterface::CONFIGURATION_IDENTIFIER => [
        'allowed_origins' => ['*//example.com', '*.example.com'],
        'allowed_headers' => ['X-Project-Header'],
        'exposed_headers' => ['X-Some-Header'],
        'allowed_max_age' => '3600',
        'credentials_allowed' => true,
    ],
    'routes' => [
          [
            'name' => 'foo-get',
            'path' => '/foo',
            'middleware' => [
                // ...
            ],
            'options' => [
                'defaults' => [
                    RouteConfigurationInterface::PARAMETER_IDENTIFIER => [
                        'explicit' => true,
                        'allowed_origins' => ['*//someotherdomain.com'],
                        'allowed_headers' => ['X-Specific-Header-For-Foo-Endpoint'],
                        'allowed_max_age' => '3600',
                    ],
                ],
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'foo-delete',
            'path' => '/foo',
            'middleware' => [
                // ...
            ],
            'allowed_methods' => ['DELETE'],
        ],
    ],
];
```

Result of this configuration for the `CORS preflight` of `/foo` for the upcoming
`GET` request will look like this:

| Configuration | Parameter |
|:------------- |:-------------:|
| `allowed_origins` | `['*//someotherdomain.com']`
| `allowed_headers` | `['X-Specific-Header-For-Foo-Endpoint']`
| `allowed_max_age` | `3600`
| `exposed_headers` | `[]`
| `credentials_allowed` | `false`
| `allowed_methods` | `['GET']`

**Did you note the missing `DELETE`? This is because of the `explicit` flag! Also note the empty `exposed_headers` which is due to the project overriding (`overrides_project_configuration`) parameter.**

#### Enable Project Merging

```php
<?php
declare(strict_types=1);

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\RouteConfigurationInterface;

return [
    ConfigurationInterface::CONFIGURATION_IDENTIFIER => [
        'allowed_origins' => ['*//example.com', '*.example.com'],
        'allowed_headers' => ['X-Project-Header'],
        'exposed_headers' => ['X-Some-Header'],
        'allowed_max_age' => '3600',
    ],
    'routes' => [
          [
            'name' => 'foo-get',
            'path' => '/foo',
            'middleware' => [
                // ...
            ],
            'options' => [
                'defaults' => [
                    RouteConfigurationInterface::PARAMETER_IDENTIFIER => [
                        'overrides_project_configuration' => false,
                        'allowed_origins' => [RouteConfigurationInterface::ANY_ORIGIN],
                        'allowed_headers' => ['X-Specific-Header-For-Foo-Endpoint'],
                        'allowed_max_age' => '7200',
                        'credentials_allowed' => true,
                    ],
                ],
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'foo-delete',
            'path' => '/foo',
            'middleware' => [
                // ...
            ],
            'allowed_methods' => ['DELETE'],
        ],
    ],
];
```

Result of this configuration for the `CORS preflight` of `/foo` for the upcoming
`GET` request will look like this:

| Configuration | Parameter |
|:-------------|:-------------:|
| `allowed_origins` | `[RouteConfigurationInterface::ANY_ORIGIN]`
| `allowed_headers` | `['X-Specific-Header-For-Foo-Endpoint', 'X-Project-Header']`
| `allowed_max_age` | `7200`
| `exposed_headers` | `['X-Some-Header']`
| `credentials_allowed` | `true`
| `allowed_methods` | `['GET', 'DELETE']`

**Did you note the `ANY_ORIGIN` detail? This is, because if `ANY_ORIGIN` is allowed for an endpoint, we remove all other origins for that route.**
