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
            ConfigurationInterface::class             => $this->createMock(ConfigurationInterface::class),
            ServerRequestFactoryInterface::class      => $this->createMock(ServerRequestFactoryInterface::class),
            RouterInterface::class                    => $this->createMock(RouterInterface::class),
            RouteConfigurationFactoryInterface::class => $this->createMock(RouteConfigurationFactoryInterface::class),
        ];
    }

    protected function factory(): callable
    {
        return new ConfigurationLocatorFactory();
    }

    protected function postCreationAssertions(mixed $instance): void
    {
        $this->assertInstanceOf(ConfigurationLocator::class, $instance);
    }
}
