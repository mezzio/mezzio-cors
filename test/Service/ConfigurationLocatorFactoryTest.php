<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Service;

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\RouteConfigurationFactoryInterface;
use Mezzio\Cors\Service\ConfigurationLocator;
use Mezzio\Cors\Service\ConfigurationLocatorFactory;
use Mezzio\CorsTest\AbstractFactoryTest;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;

final class ConfigurationLocatorFactoryTest extends AbstractFactoryTest
{
    protected function dependencies(): array
    {
        return [
            ConfigurationInterface::class             => ConfigurationInterface::class,
            ServerRequestFactoryInterface::class      => ServerRequestFactoryInterface::class,
            RouterInterface::class                    => RouterInterface::class,
            RouteConfigurationFactoryInterface::class => RouteConfigurationFactoryInterface::class,
        ];
    }

    protected function factory(): callable
    {
        return new ConfigurationLocatorFactory();
    }

    /**
     * Implement this for post creation assertions.
     *
     * @param mixed $instance
     */
    protected function postCreationAssertions($instance): void
    {
        $this->assertInstanceOf(ConfigurationLocator::class, $instance);
    }
}
