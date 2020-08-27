<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Middleware;

use Mezzio\Cors\Middleware\CorsMiddleware;
use Mezzio\Cors\Middleware\CorsMiddlewareFactory;
use Mezzio\Cors\Service\ConfigurationLocatorInterface;
use Mezzio\Cors\Service\CorsInterface;
use Mezzio\Cors\Service\ResponseFactoryInterface;
use Mezzio\CorsTest\AbstractFactoryTest;

final class CorsMiddlewareFactoryTest extends AbstractFactoryTest
{
    /**
     * @return array<string,string>
     */
    protected function dependencies(): array
    {
        return [
            CorsInterface::class                 => CorsInterface::class,
            ConfigurationLocatorInterface::class => ConfigurationLocatorInterface::class,
            ResponseFactoryInterface::class      => ResponseFactoryInterface::class,
        ];
    }

    protected function factory(): callable
    {
        return new CorsMiddlewareFactory();
    }

    /**
     * Implement this for post creation assertions.
     *
     * @param mixed $instance
     */
    protected function postCreationAssertions($instance): void
    {
        $this->assertInstanceOf(CorsMiddleware::class, $instance);
    }
}
