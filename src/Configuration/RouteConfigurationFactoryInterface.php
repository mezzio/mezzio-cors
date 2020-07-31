<?php

declare(strict_types=1);

namespace Mezzio\Cors\Configuration;

interface RouteConfigurationFactoryInterface
{
    public function __invoke(array $parameters): RouteConfigurationInterface;
}
