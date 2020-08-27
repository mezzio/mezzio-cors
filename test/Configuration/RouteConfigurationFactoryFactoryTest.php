<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Configuration;

use Mezzio\Cors\Configuration\RouteConfigurationFactory;
use Mezzio\Cors\Configuration\RouteConfigurationFactoryFactory;
use Mezzio\CorsTest\AbstractFactoryTest;

final class RouteConfigurationFactoryFactoryTest extends AbstractFactoryTest
{
    protected function dependencies(): array
    {
        return [];
    }

    protected function factory(): callable
    {
        return new RouteConfigurationFactoryFactory();
    }

    /**
     * Implement this for post creation assertions.
     *
     * @param mixed $instance
     */
    protected function postCreationAssertions($instance): void
    {
        $this->assertInstanceOf(RouteConfigurationFactory::class, $instance);
    }
}
