<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Configuration;

use Mezzio\Cors\Configuration\Exception\InvalidConfigurationException;
use Mezzio\Cors\Configuration\ProjectConfiguration;
use PHPUnit\Framework\TestCase;

use function lcfirst;
use function str_replace;
use function ucwords;

final class ProjectConfigurationTest extends TestCase
{
    public function testConstructorWillSetProperties(): void
    {
        $parameters = [
            'allowed_origins'     => ['foo'],
            'allowed_headers'     => ['baz'],
            'allowed_max_age'     => '123',
            'credentials_allowed' => true,
            'exposed_headers'     => ['foo', 'bar', 'baz'],
        ];
        $config     = new ProjectConfiguration($parameters);

        $this->assertSame(['foo'], $config->allowedOrigins());
        $this->assertSame(['baz'], $config->allowedHeaders());
        $this->assertSame('123', $config->allowedMaxAge());
        $this->assertTrue($config->credentialsAllowed());
        $this->assertSame(['foo', 'bar', 'baz'], $config->exposedHeaders());

        $camelCasedParameters = [];
        foreach ($parameters as $parameter => $value) {
            $camelCasedParameter                        = str_replace(
                ' ',
                '',
                lcfirst(ucwords(str_replace('_', ' ', $parameter)))
            );
            $camelCasedParameters[$camelCasedParameter] = $value;
        }

        $config = new ProjectConfiguration($camelCasedParameters);
        $this->assertSame(['foo'], $config->allowedOrigins());
        $this->assertSame(['baz'], $config->allowedHeaders());
        $this->assertSame('123', $config->allowedMaxAge());
        $this->assertTrue($config->credentialsAllowed());
        $this->assertSame(['foo', 'bar', 'baz'], $config->exposedHeaders());
    }

    public function testWillThrowExceptionOnUnknownParameter()
    {
        $this->expectException(InvalidConfigurationException::class);
        new ProjectConfiguration(['foo' => 'bar']);
    }
}
