<?php

declare(strict_types=1);

namespace Mezzio\Cors\Configuration;

final class RouteConfiguration extends AbstractConfiguration implements RouteConfigurationInterface
{
    private bool $overridesProjectConfiguration = true;

    private bool $explicit = false;

    public function setOverridesProjectConfiguration(bool $overridesProjectConfiguration): void
    {
        $this->overridesProjectConfiguration = $overridesProjectConfiguration;
    }

    /**
     * MUST return true if the projects config may be overriden. If it returns false, the project config will get
     * merged.
     */
    public function overridesProjectConfiguration(): bool
    {
        return $this->overridesProjectConfiguration;
    }

    /**
     * @inheritDoc
     */
    public function explicit(): bool
    {
        return $this->explicit;
    }

    public function setExplicit(bool $explicit): void
    {
        $this->explicit = $explicit;
    }

    public function mergeWithConfiguration(ConfigurationInterface $configuration): RouteConfigurationInterface
    {
        if ($configuration === $this) {
            return $configuration;
        }

        $instance = clone $this;

        if (! $instance->credentialsAllowed()) {
            $instance->setCredentialsAllowed($configuration->credentialsAllowed());
        }

        if ($instance->allowedMaxAge() === ConfigurationInterface::PREFLIGHT_CACHE_DISABLED) {
            $instance->setAllowedMaxAge($configuration->allowedMaxAge());
        }

        $instance->setAllowedHeaders([...$configuration->allowedHeaders(), ...$instance->allowedHeaders()]);
        $instance->setAllowedOrigins([...$configuration->allowedOrigins(), ...$instance->allowedOrigins()]);
        $instance->setExposedHeaders([...$configuration->exposedHeaders(), ...$instance->exposedHeaders()]);
        $instance->setAllowedMethods([...$configuration->allowedMethods(), ...$instance->allowedMethods()]);

        return $instance;
    }

    /**
     * Should merge the request methods.
     *
     * @psalm-param list<string> $methods
     */
    public function withRequestMethods(array $methods): RouteConfigurationInterface
    {
        $instance = clone $this;
        $instance->setAllowedMethods([...$this->allowedMethods, ...$methods]);

        return $instance;
    }
}
