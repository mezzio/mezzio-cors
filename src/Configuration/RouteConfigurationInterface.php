<?php

declare(strict_types=1);

namespace Mezzio\Cors\Configuration;

interface RouteConfigurationInterface extends ConfigurationInterface
{
    /**
     * Identifier to locate the route configuration from the matched parameters of a route.
     */
    public const PARAMETER_IDENTIFIER = 'cors';

    /**
     * MUST return true if the projects config may be overriden. If it returns false, the project config will get
     * merged.
     * The default of this method should return true if not set otherwise by the configuration inside the route.
     */
    public function overridesProjectConfiguration(): bool;

    /**
     * Marks a route as an explicit endpoint.
     * Explicit endpoint means, that this route is the only route which will match for a specific request.
     * The default of this method should always return false unless a route is really explicit.
     */
    public function explicit(): bool;

    /**
     * Should merge the request methods.
     *
     * @psalm-param list<string> $methods
     */
    public function withRequestMethods(array $methods): self;

    /**
     * Should merge route configuration with the project configuration.
     */
    public function mergeWithConfiguration(ConfigurationInterface $configuration): self;
}
