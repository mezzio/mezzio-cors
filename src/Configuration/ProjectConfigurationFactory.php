<?php

declare(strict_types=1);

namespace Mezzio\Cors\Configuration;

use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

final class ProjectConfigurationFactory
{
    public function __invoke(ContainerInterface $container): ProjectConfiguration
    {
        $parameters = $container->get('config')[ProjectConfiguration::CONFIGURATION_IDENTIFIER] ?? [];
        Assert::isMap($parameters);
        return new ProjectConfiguration($parameters);
    }
}
