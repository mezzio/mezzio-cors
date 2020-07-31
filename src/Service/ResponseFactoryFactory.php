<?php

declare(strict_types=1);

namespace Mezzio\Cors\Service;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface as PsrResponseFactoryInterface;

final class ResponseFactoryFactory
{
    public function __invoke(ContainerInterface $container) : ResponseFactory
    {
        return new ResponseFactory($container->get(PsrResponseFactoryInterface::class));
    }
}
