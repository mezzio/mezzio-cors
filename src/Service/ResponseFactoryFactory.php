<?php

declare(strict_types=1);

namespace Mezzio\Cors\Service;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface as PsrResponseFactoryInterface;

use function assert;

final class ResponseFactoryFactory
{
    public function __invoke(ContainerInterface $container): ResponseFactory
    {
        $responseFactory = $container->get(PsrResponseFactoryInterface::class);
        assert($responseFactory instanceof PsrResponseFactoryInterface);
        return new ResponseFactory($responseFactory);
    }
}
