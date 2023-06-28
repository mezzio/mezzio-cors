<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Configuration;

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\ProjectConfiguration;
use Mezzio\Cors\Configuration\ProjectConfigurationFactory;
use Mezzio\CorsTest\AbstractFactoryTestCase;

final class ProjectConfigurationFactoryTest extends AbstractFactoryTestCase
{
    protected function dependencies(): array
    {
        return [
            'config' => [ConfigurationInterface::CONFIGURATION_IDENTIFIER => ['exposed_headers' => ['X-Foo']]],
        ];
    }

    protected function factory(): callable
    {
        return new ProjectConfigurationFactory();
    }

    protected function postCreationAssertions(mixed $instance): void
    {
        $this->assertInstanceOf(ProjectConfiguration::class, $instance);
        $this->assertEquals(['X-Foo'], $instance->exposedHeaders());
    }
}
