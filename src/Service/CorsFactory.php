<?php

declare(strict_types=1);

namespace Mezzio\Cors\Service;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\UriFactoryInterface;

final class CorsFactory
{
    public function __invoke(ContainerInterface $container): Cors
    {
        return new Cors($container->get(UriFactoryInterface::class));
    }
}
