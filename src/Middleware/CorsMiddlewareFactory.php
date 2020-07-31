<?php

declare(strict_types=1);

namespace Mezzio\Cors\Middleware;

use Mezzio\Cors\Service\ConfigurationLocatorInterface;
use Mezzio\Cors\Service\CorsInterface;
use Mezzio\Cors\Service\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;

final class CorsMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : CorsMiddleware
    {
        return new CorsMiddleware(
            $container->get(CorsInterface::class),
            $container->get(ConfigurationLocatorInterface::class),
            $container->get(ResponseFactoryInterface::class)
        );
    }
}
