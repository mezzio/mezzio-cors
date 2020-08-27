<?php

declare(strict_types=1);

namespace Mezzio\Cors\Configuration;

use Mezzio\Cors\Service\CorsMetadata;
use Webmozart\Assert\Assert;

use function array_merge;
use function array_unique;
use function array_values;
use function sort;

use const SORT_ASC;
use const SORT_STRING;

final class RouteConfiguration extends AbstractConfiguration implements RouteConfigurationInterface
{
    /** @var bool */
    protected $overridesProjectConfiguration = true;

    /** @var bool */
    protected $explicit = false;

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

        if (! $instance->allowedMaxAge()) {
            $instance->setAllowedMaxAge($configuration->allowedMaxAge());
        }

        $instance->setAllowedHeaders(
            array_values(
                array_merge($configuration->allowedHeaders(), $instance->allowedHeaders())
            )
        );
        $instance->setAllowedOrigins(
            array_values(
                array_merge($configuration->allowedOrigins(), $instance->allowedOrigins())
            )
        );
        $instance->setExposedHeaders(
            array_values(
                array_merge($configuration->exposedHeaders(), $instance->exposedHeaders())
            )
        );

        return $instance->withRequestMethods($configuration->allowedMethods());
    }

    /**
     * Should merge the request methods.
     *
     * @psalm-param list<string> $methods
     */
    public function withRequestMethods(array $methods): RouteConfigurationInterface
    {
        $methods = $this->normalizeRequestMethods(array_merge($this->allowedMethods, $methods));

        $instance                 = clone $this;
        $instance->allowedMethods = $methods;

        return $instance;
    }

    /**
     * @param array<int|string,string> $methods
     * @psalm-return list<string>
     */
    private function normalizeRequestMethods(array $methods): array
    {
        Assert::allOneOf($methods, CorsMetadata::ALLOWED_REQUEST_METHODS);

        $methods = array_unique($methods);
        sort($methods, SORT_ASC | SORT_STRING);

        return array_values($methods);
    }
}
