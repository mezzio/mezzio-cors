# Quick Start

## Setting up the `CorsMiddleware`

After installing this component, the `CorsMiddleware` has to be added to the pipeline configuration.

Depending on the configuration style you've chosen for the project, the pipeline might be either an array (in combination with the `ApplicationConfigInjectionDelegator`) or an anonymous function.

> ### Positioning of the `CorsMiddleware` is Crucial
>
> The `CorsMiddleware` **MUST** be added to the pipeline **before** the `RouteMiddleware`.

More details about the `CorsMiddleware` can be found [here](middleware.md).

### Anonymous Function Pipeline

```php
use Mezzio\Application;
use Mezzio\Cors\Middleware\CorsMiddleware;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use Psr\Container\ContainerInterface;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {
    // [...] 
    $app->pipe(CorsMiddleware::class);
    // [...]
    $app->pipe(RouteMiddleware::class);
    // [...]
    $app->pipe(DispatchMiddleware::class);
    // [...]
};
```

### Config Injection Pipeline

```php
use Mezzio\Application;
use Mezzio\Container\ApplicationConfigInjectionDelegator;
use Mezzio\Cors\Middleware\CorsMiddleware;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;

return [
    'dependencies' => [
        'delegators' => [
            Application::class => [ApplicationConfigInjectionDelegator::class],
        ],
    ],
    'middleware_pipeline' => [
        // [...]
        ['middleware' => CorsMiddleware::class],
        // [...]
        ['middleware' => RouteMiddleware::class],
        // [...]
        ['middleware' => DispatchMiddleware::class],
        // [...]
    ],
];
```

## Setting Up Configuration

After setting up the pipeline, a configuration is needed.
Depending on how granular you want to add permissions, you need to add either a [project based configuration](middleware.md#examples-for-project-configurations) file or a [per-route configuration](middleware.md#examples-for-route-configurations).
