<?php

declare(strict_types=1);

namespace Mezzio\Cors\Service;

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\RouteConfigurationFactoryInterface;
use Mezzio\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;

use function assert;

final class ConfigurationLocatorFactory
{
    public function __invoke(ContainerInterface $container): ConfigurationLocator
    {
        $configuration = $container->get(ConfigurationInterface::class);
        assert($configuration instanceof ConfigurationInterface);
        $requestFactory = $container->get(ServerRequestFactoryInterface::class);
        assert($requestFactory instanceof ServerRequestFactoryInterface);
        $router = $container->get(RouterInterface::class);
        assert($router instanceof RouterInterface);
        $routeConfigurationFactory = $container->get(RouteConfigurationFactoryInterface::class);
        assert($routeConfigurationFactory instanceof RouteConfigurationFactoryInterface);

        return new ConfigurationLocator(
            $configuration,
            $requestFactory,
            $router,
            $routeConfigurationFactory
        );
    }
}
