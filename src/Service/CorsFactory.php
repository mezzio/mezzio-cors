<?php

declare(strict_types=1);

namespace Mezzio\Cors\Service;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\UriFactoryInterface;

use function assert;

final class CorsFactory
{
    public function __invoke(ContainerInterface $container): Cors
    {
        $uriFactory = $container->get(UriFactoryInterface::class);
        assert($uriFactory instanceof UriFactoryInterface);
        return new Cors($uriFactory);
    }
}
