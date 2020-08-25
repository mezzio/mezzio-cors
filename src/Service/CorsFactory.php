<?php

declare(strict_types=1);

namespace Mezzio\Cors\Service;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\UriFactoryInterface;
use Webmozart\Assert\Assert;

final class CorsFactory
{
    public function __invoke(ContainerInterface $container): Cors
    {
        $uriFactory = $container->get(UriFactoryInterface::class);
        Assert::isInstanceOf($uriFactory, UriFactoryInterface::class);
        return new Cors($uriFactory);
    }
}
