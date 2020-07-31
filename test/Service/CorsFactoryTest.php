<?php
declare(strict_types=1);

namespace Mezzio\CorsTest\Service;

use Mezzio\Cors\Service\Cors;
use Mezzio\Cors\Service\CorsFactory;
use Mezzio\CorsTest\AbstractFactoryTest;
use Psr\Http\Message\UriFactoryInterface;

final class CorsFactoryTest extends AbstractFactoryTest
{

    /**
     * @return array<string,string|array|object>
     */
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

    /**
     * Implement this for post creation assertions.
     */
    protected function postCreationAssertions($instance): void
    {
        $this->assertInstanceOf(Cors::class, $instance);
    }
}
