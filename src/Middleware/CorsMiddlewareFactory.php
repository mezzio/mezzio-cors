<?php

declare(strict_types=1);

namespace Mezzio\Cors\Middleware;

use Mezzio\Cors\Service\ConfigurationLocatorInterface;
use Mezzio\Cors\Service\CorsInterface;
use Mezzio\Cors\Service\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;

use function assert;

final class CorsMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): CorsMiddleware
    {
        $cors = $container->get(CorsInterface::class);
        assert($cors instanceof CorsInterface);
        $configurationLocator = $container->get(ConfigurationLocatorInterface::class);
        assert($configurationLocator instanceof ConfigurationLocatorInterface);
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);
        return new CorsMiddleware(
            $cors,
            $configurationLocator,
            $responseFactory
        );
    }
}
