<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Configuration;

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\RouteConfigurationFactory;
use PHPUnit\Framework\TestCase;

final class RouteConfigurationFactoryTest extends TestCase
{
    private RouteConfigurationFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new RouteConfigurationFactory();
    }

    public function testWillInstantiateRouteConfiguration(): void
    {
        $factory  = $this->factory;
        $instance = $factory([]);
        self::assertEquals(ConfigurationInterface::PREFLIGHT_CACHE_DISABLED, $instance->allowedMaxAge());
        self::assertEmpty($instance->allowedMethods());
        self::assertEmpty($instance->allowedOrigins());
        self::assertEmpty($instance->allowedHeaders());
        self::assertEmpty($instance->exposedHeaders());
        self::assertFalse($instance->credentialsAllowed());
    }
}
