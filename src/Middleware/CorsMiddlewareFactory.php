<?php

declare(strict_types=1);

namespace Mezzio\Cors\Middleware;

use Mezzio\Cors\Service\ConfigurationLocatorInterface;
use Mezzio\Cors\Service\CorsInterface;
use Mezzio\Cors\Service\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

final class CorsMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): CorsMiddleware
    {
        $cors = $container->get(CorsInterface::class);
        Assert::isInstanceOf($cors, CorsInterface::class);
        $configurationLocator = $container->get(ConfigurationLocatorInterface::class);
        Assert::isInstanceOf($configurationLocator, ConfigurationLocatorInterface::class);
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        Assert::isInstanceOf($responseFactory, ResponseFactoryInterface::class);
        return new CorsMiddleware(
            $cors,
            $configurationLocator,
            $responseFactory
        );
    }
}
