<?php

declare(strict_types=1);

namespace Mezzio\Cors;

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\ProjectConfiguration;
use Mezzio\Cors\Configuration\ProjectConfigurationFactory;
use Mezzio\Cors\Configuration\RouteConfigurationFactory;
use Mezzio\Cors\Configuration\RouteConfigurationFactoryFactory;
use Mezzio\Cors\Configuration\RouteConfigurationFactoryInterface;
use Mezzio\Cors\Middleware\CorsMiddleware;
use Mezzio\Cors\Middleware\CorsMiddlewareFactory;
use Mezzio\Cors\Service\ConfigurationLocator;
use Mezzio\Cors\Service\ConfigurationLocatorFactory;
use Mezzio\Cors\Service\ConfigurationLocatorInterface;
use Mezzio\Cors\Service\Cors;
use Mezzio\Cors\Service\CorsFactory;
use Mezzio\Cors\Service\CorsInterface;
use Mezzio\Cors\Service\ResponseFactory;
use Mezzio\Cors\Service\ResponseFactoryFactory;
use Mezzio\Cors\Service\ResponseFactoryInterface;

final class ConfigProvider
{
    /**
     * @return array<string,mixed>
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getServiceDependencies(),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function getServiceDependencies(): array
    {
        return [
            'factories' => [
                ProjectConfiguration::class      => ProjectConfigurationFactory::class,
                CorsMiddleware::class            => CorsMiddlewareFactory::class,
                ConfigurationLocator::class      => ConfigurationLocatorFactory::class,
                Cors::class                      => CorsFactory::class,
                ResponseFactory::class           => ResponseFactoryFactory::class,
                RouteConfigurationFactory::class => RouteConfigurationFactoryFactory::class,
            ],
            'aliases'   => [
                ConfigurationLocatorInterface::class      => ConfigurationLocator::class,
                ConfigurationInterface::class             => ProjectConfiguration::class,
                CorsInterface::class                      => Cors::class,
                ResponseFactoryInterface::class           => ResponseFactory::class,
                RouteConfigurationFactoryInterface::class => RouteConfigurationFactory::class,
            ],
        ];
    }
}
