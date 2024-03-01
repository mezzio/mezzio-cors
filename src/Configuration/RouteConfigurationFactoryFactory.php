<?php

declare(strict_types=1);

namespace Mezzio\Cors\Configuration;

use Psr\Container\ContainerInterface;

final class RouteConfigurationFactoryFactory
{
    /** @psalm-suppress UnusedParam */
    public function __invoke(ContainerInterface $container): RouteConfigurationFactory
    {
        return new RouteConfigurationFactory();
    }
}
