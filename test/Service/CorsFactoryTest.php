<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Service;

use Mezzio\Cors\Service\Cors;
use Mezzio\Cors\Service\CorsFactory;
use Mezzio\CorsTest\AbstractFactoryTest;
use Psr\Http\Message\UriFactoryInterface;

final class CorsFactoryTest extends AbstractFactoryTest
{
    protected function dependencies(): array
    {
        return [
            UriFactoryInterface::class => UriFactoryInterface::class,
        ];
    }

    protected function factory(): callable
    {
        return new CorsFactory();
    }

    protected function postCreationAssertions($instance): void
    {
        $this->assertInstanceOf(Cors::class, $instance);
    }
}
