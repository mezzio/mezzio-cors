<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Service;

use Mezzio\Cors\Service\ResponseFactory;
use Mezzio\Cors\Service\ResponseFactoryFactory;
use Mezzio\CorsTest\AbstractFactoryTestCase;
use Psr\Http\Message\ResponseFactoryInterface;

final class ResponseFactoryFactoryTest extends AbstractFactoryTestCase
{
    protected function dependencies(): array
    {
        return [
            ResponseFactoryInterface::class => $this->createMock(ResponseFactoryInterface::class),
        ];
    }

    protected function factory(): callable
    {
        return new ResponseFactoryFactory();
    }

    protected function postCreationAssertions(mixed $instance): void
    {
        $this->assertInstanceOf(ResponseFactory::class, $instance);
    }
}
