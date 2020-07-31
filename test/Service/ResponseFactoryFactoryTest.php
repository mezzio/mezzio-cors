<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Service;

use Mezzio\Cors\Service\ResponseFactory;
use Mezzio\Cors\Service\ResponseFactoryFactory;
use Mezzio\CorsTest\AbstractFactoryTest;
use Psr\Http\Message\ResponseFactoryInterface;

final class ResponseFactoryFactoryTest extends AbstractFactoryTest
{
    /**
     * @return array<string,string|array|object>
     */
    protected function dependencies(): array
    {
        return [
            ResponseFactoryInterface::class => ResponseFactoryInterface::class,
        ];
    }

    protected function factory(): callable
    {
        return new ResponseFactoryFactory();
    }

    /**
     * Implement this for post creation assertions.
     *
     * @param mixed $instance
     */
    protected function postCreationAssertions($instance): void
    {
        $this->assertInstanceOf(ResponseFactory::class, $instance);
    }
}
