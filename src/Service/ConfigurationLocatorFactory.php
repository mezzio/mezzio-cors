<?php

declare(strict_types=1);

namespace Mezzio\Cors\Service;

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\RouteConfigurationFactoryInterface;
use Mezzio\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Webmozart\Assert\Assert;

final class ConfigurationLocatorFactory
{
    public function __invoke(ContainerInterface $container): ConfigurationLocator
    {
        $configuration = $container->get(ConfigurationInterface::class);
        Assert::isInstanceOf($configuration, ConfigurationInterface::class);
        $requestFactory = $container->get(ServerRequestFactoryInterface::class);
        Assert::isInstanceOf($requestFactory, ServerRequestFactoryInterface::class);
        $router = $container->get(RouterInterface::class);
        Assert::isInstanceOf($router, RouterInterface::class);
        $routeConfigurationFactory = $container->get(RouteConfigurationFactoryInterface::class);
        Assert::isInstanceOf($routeConfigurationFactory, RouteConfigurationFactoryInterface::class);

        return new ConfigurationLocator(
            $configuration,
            $requestFactory,
            $router,
            $routeConfigurationFactory
        );
    }
}
