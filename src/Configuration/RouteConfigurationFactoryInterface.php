<?php

declare(strict_types=1);

namespace Mezzio\Cors\Configuration;

interface RouteConfigurationFactoryInterface
{
    /**
     * @psalm-param array<string,mixed> $parameters
     */
    public function __invoke(array $parameters): RouteConfigurationInterface;
}
