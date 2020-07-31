<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Configuration;

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\ProjectConfiguration;
use Mezzio\Cors\Configuration\RouteConfiguration;
use PHPUnit\Framework\TestCase;

use function in_array;
use function sprintf;

final class RouteConfigurationTest extends TestCase
{
    public function testConstructorWillSetProperties(): void
    {
        $parameters = [
            'overrides_project_configuration' => false,
            'allowed_origins'                 => ['foo'],
            'allowed_headers'                 => ['baz'],
            'allowed_max_age'                 => '123',
            'credentials_allowed'             => true,
            'exposed_headers'                 => ['foo', 'bar', 'baz'],
        ];
        $config     = new RouteConfiguration($parameters);

        $this->assertFalse($config->overridesProjectConfiguration());
        $this->assertSame(['foo'], $config->allowedOrigins());
        $this->assertSame([], $config->allowedMethods());
        $this->assertSame(['baz'], $config->allowedHeaders());
        $this->assertSame('123', $config->allowedMaxAge());
        $this->assertTrue($config->credentialsAllowed());
        $this->assertSame(['foo', 'bar', 'baz'], $config->exposedHeaders());
    }

    public function testWillMergeAnyValueFromConfiguration(): void
    {
        $routeConfiguration = new RouteConfiguration([]);
        $config             = $this->createMock(ConfigurationInterface::class);

        $config
            ->expects($this->once())
            ->method('credentialsAllowed')
            ->willReturn(true);

        $config
            ->expects($this->once())
            ->method('allowedMaxAge')
            ->willReturn('1');

        $config
            ->expects($this->once())
            ->method('allowedHeaders')
            ->willReturn(['Foo']);

        $config
            ->expects($this->once())
            ->method('allowedOrigins')
            ->willReturn(['http://www.example.org']);

        $config
            ->expects($this->once())
            ->method('exposedHeaders')
            ->willReturn(['X-Bar']);

        $config
            ->expects($this->once())
            ->method('allowedMethods')
            ->willReturn(['GET']);

        $immutable = $routeConfiguration->mergeWithConfiguration($config);
        $this->assertNotSame($immutable, $routeConfiguration);
        $this->assertEquals(true, $immutable->credentialsAllowed());
        $this->assertEquals('1', $immutable->allowedMaxAge());
        $this->assertEquals(['Foo'], $immutable->allowedHeaders());
        $this->assertEquals(['http://www.example.org'], $immutable->allowedOrigins());
        $this->assertEquals(['X-Bar'], $immutable->exposedHeaders());
        $this->assertEquals(['GET'], $immutable->allowedMethods());
    }

    public function testWillMergeRequestMethods(): void
    {
        $routeConfiguration = new RouteConfiguration([]);
        $routeConfiguration = $routeConfiguration
            ->withRequestMethods(['GET'])
            ->withRequestMethods(['POST'])
            ->withRequestMethods(['HEAD', 'DELETE']);

        $this->assertEquals(['DELETE', 'GET', 'HEAD', 'POST'], $routeConfiguration->allowedMethods());
    }

    public function testWillMergeMultipleConfigurations()
    {
        $routeConfiguration = (new RouteConfiguration([
            'allowed_headers' => ['X-Baz'],
            'exposed_headers' => ['X-Special-Header'],
            'allowed_origins' => ['whatever'],
        ]))
            ->withRequestMethods(['GET']);

        $routeConfigurationWithSomeAllowedHeaders                                 = new RouteConfiguration([
            'allowed_headers' => ['X-Foo', 'X-Bar'],
        ]);
        $routeConfigurationWithSomeAllowedOrigins                                 = new RouteConfiguration([
            'allowed_origins' => ['foobar'],
        ]);
        $routeConfigurationWithWildcardAllowedOrigins                             = new RouteConfiguration([
            'allowed_origins' => ['*'], // Wildcard will drop any other allowed origin
        ]);
        $routeConfigurationWithSomeExposedHeaders                                 = new RouteConfiguration([
            'exposed_headers' => ['X-Another-Header'],
        ]);
        $routeConfigurationWithSomeRequestMethods                                 = (new RouteConfiguration([]))->withRequestMethods([
            'POST',
        ]);
        $routeConfigurationWithAllowedMaxAge                                      = new RouteConfiguration([
            'allowed_max_age' => '12345',
        ]);
        $routeConfigurationWithHigherMaxAgeButIgnoredDueToAlreadySetMaxAge        = new RouteConfiguration([
            'allowed_max_age' => '54321',
        ]);
        $routeConfigurationWithCredentialsAllowed                                 = new RouteConfiguration([
            'credentials_allowed' => true,
        ]);
        $routeConfigurationWithCredentialsDisallowedButIngoredDueToAlreadyAllowed = new RouteConfiguration([
            'credentials_allowed' => false,
        ]);

        $routeConfiguration = $routeConfiguration
            ->mergeWithConfiguration($routeConfigurationWithSomeAllowedHeaders)
            ->mergeWithConfiguration($routeConfigurationWithSomeAllowedOrigins)
            ->mergeWithConfiguration($routeConfigurationWithWildcardAllowedOrigins)
            ->mergeWithConfiguration($routeConfigurationWithSomeExposedHeaders)
            ->mergeWithConfiguration($routeConfigurationWithSomeRequestMethods)
            ->mergeWithConfiguration($routeConfigurationWithAllowedMaxAge)
            ->mergeWithConfiguration($routeConfigurationWithHigherMaxAgeButIgnoredDueToAlreadySetMaxAge)
            ->mergeWithConfiguration($routeConfigurationWithCredentialsAllowed)
            ->mergeWithConfiguration($routeConfigurationWithCredentialsDisallowedButIngoredDueToAlreadyAllowed);

        $this->assertTrue($routeConfiguration->credentialsAllowed());
        $this->assertEquals('12345', $routeConfiguration->allowedMaxAge());
        foreach (['X-Baz', 'X-Foo', 'X-Bar'] as $header) {
            $this->assertTrue(in_array($header, $routeConfiguration->allowedHeaders(), true), sprintf('Missing header %s', $header));
        }
        $this->assertEquals(['*'], $routeConfiguration->allowedOrigins());
        $this->assertEquals(['X-Another-Header', 'X-Special-Header'], $routeConfiguration->exposedHeaders());
        $this->assertEquals(['GET', 'POST'], $routeConfiguration->allowedMethods());
    }

    public function testWillMergeAllowedHeadersWithoutDuplicates()
    {
        $project = new ProjectConfiguration(['allowed_headers' => ['X-Foo']]);
        $route   = new RouteConfiguration(['allowed_headers' => ['X-Foo']]);
        $merged  = $route->mergeWithConfiguration($project);
        $this->assertEquals($merged->allowedHeaders(), ['X-Foo']);
    }

    public function testWillProvideExplicitFromParameters()
    {
        $routeConfiguration = new RouteConfiguration(['explicit' => true]);
        $this->assertTrue($routeConfiguration->explicit());
    }

    public function testWontMergeItself()
    {
        $routeConfiguration = new RouteConfiguration([]);
        $routeConfiguration = $routeConfiguration->mergeWithConfiguration($routeConfiguration);

        $this->assertSame($routeConfiguration, $routeConfiguration);
    }
}
