<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Middleware;

use Mezzio\Cors\Middleware\CorsMiddleware;
use Mezzio\Cors\Middleware\CorsMiddlewareFactory;
use Mezzio\Cors\Service\ConfigurationLocatorInterface;
use Mezzio\Cors\Service\CorsInterface;
use Mezzio\Cors\Service\ResponseFactoryInterface;
use Mezzio\CorsTest\AbstractFactoryTestCase;

final class CorsMiddlewareFactoryTest extends AbstractFactoryTestCase
{
    protected function dependencies(): array
    {
        return [
            CorsInterface::class                 => $this->createMock(CorsInterface::class),
            ConfigurationLocatorInterface::class => $this->createMock(ConfigurationLocatorInterface::class),
            ResponseFactoryInterface::class      => $this->createMock(ResponseFactoryInterface::class),
        ];
    }

    protected function factory(): callable
    {
        return new CorsMiddlewareFactory();
    }

    protected function postCreationAssertions(mixed $instance): void
    {
        $this->assertInstanceOf(CorsMiddleware::class, $instance);
    }
}
