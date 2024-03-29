<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Configuration;

use Mezzio\Cors\Configuration\RouteConfigurationFactory;
use Mezzio\Cors\Configuration\RouteConfigurationFactoryFactory;
use Mezzio\CorsTest\AbstractFactoryTestCase;

final class RouteConfigurationFactoryFactoryTest extends AbstractFactoryTestCase
{
    protected function dependencies(): array
    {
        return [];
    }

    protected function factory(): callable
    {
        return new RouteConfigurationFactoryFactory();
    }

    protected function postCreationAssertions(mixed $instance): void
    {
        $this->assertInstanceOf(RouteConfigurationFactory::class, $instance);
    }
}
