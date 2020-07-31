<?php

declare(strict_types=1);

namespace Mezzio\Cors\Service;

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\RouteConfigurationFactoryInterface;
use Mezzio\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;

final class ConfigurationLocatorFactory
{
    public function __invoke(ContainerInterface $container): ConfigurationLocator
    {
        return new ConfigurationLocator(
            $container->get(ConfigurationInterface::class),
            $container->get(ServerRequestFactoryInterface::class),
            $container->get(RouterInterface::class),
            $container->get(RouteConfigurationFactoryInterface::class)
        );
    }
}
