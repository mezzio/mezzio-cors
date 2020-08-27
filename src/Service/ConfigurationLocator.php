<?php

declare(strict_types=1);

namespace Mezzio\Cors\Service;

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\RouteConfigurationFactoryInterface;
use Mezzio\Cors\Configuration\RouteConfigurationInterface;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Webmozart\Assert\Assert;

use function array_diff;
use function array_merge;
use function array_values;

final class ConfigurationLocator implements ConfigurationLocatorInterface
{
    /** @var ConfigurationInterface */
    private $configuration;

    /** @var ServerRequestFactoryInterface */
    private $requestFactory;

    /** @var RouterInterface */
    private $router;

    /** @var RouteConfigurationFactoryInterface */
    private $routeConfigurationFactory;

    public function __construct(
        ConfigurationInterface $configuration,
        ServerRequestFactoryInterface $requestFactory,
        RouterInterface $router,
        RouteConfigurationFactoryInterface $routeConfigurationFactory
    ) {
        $this->configuration             = $configuration;
        $this->requestFactory            = $requestFactory;
        $this->router                    = $router;
        $this->routeConfigurationFactory = $routeConfigurationFactory;
    }

    /**
     * @inheritDoc
     */
    public function locate(CorsMetadata $metadata): ?ConfigurationInterface
    {
        $factory       = $this->routeConfigurationFactory;
        $configuration = $factory([])->mergeWithConfiguration($this->configuration);

        // Move the requested method to the top so it will be the first one tried to match
        $requestMethods = array_merge([$metadata->requestedMethod], array_diff(
            CorsMetadata::ALLOWED_REQUEST_METHODS,
            [$metadata->requestedMethod]
        ));

        $anyRouteIsMatching = false;
        foreach ($requestMethods as $method) {
            $request = $this->requestFactory->createServerRequest($method, $metadata->requestedUri);
            $route   = $this->router->match($request);
            if ($route->isFailure()) {
                continue;
            }

            $anyRouteIsMatching         = true;
            $routeSpecificConfiguration = $this->configurationFromRoute($route);

            if ($routeSpecificConfiguration->explicit()) {
                return $routeSpecificConfiguration;
            }

            $configuration = $configuration->mergeWithConfiguration($routeSpecificConfiguration);
        }

        if (! $anyRouteIsMatching) {
            return null;
        }

        return $configuration;
    }

    private function configurationFromRoute(RouteResult $result): RouteConfigurationInterface
    {
        $allowedMethods = array_values($result->getAllowedMethods() ?? []);
        if ($allowedMethods === []) {
            $allowedMethods = CorsMetadata::ALLOWED_REQUEST_METHODS;
        }

        $explicit                  = $this->explicit($allowedMethods);
        $routeConfigurationFactory = $this->routeConfigurationFactory;

        $routeParameters = $result->getMatchedParams()[RouteConfigurationInterface::PARAMETER_IDENTIFIER] ?? null;
        Assert::nullOrIsMap($routeParameters);
        if ($routeParameters === null) {
            return $routeConfigurationFactory(['explicit' => $explicit])
                ->mergeWithConfiguration($this->configuration)
                ->withRequestMethods($allowedMethods);
        }

        $routeParameters = ['explicit' => $explicit] + $routeParameters;

        $routeConfiguration = $routeConfigurationFactory($routeParameters)
            ->withRequestMethods($allowedMethods);

        if ($routeConfiguration->overridesProjectConfiguration()) {
            return $routeConfiguration;
        }

        return $routeConfiguration->mergeWithConfiguration($this->configuration);
    }

    /**
     * @psalm-param list<string> $allowedMethods
     */
    private function explicit(array $allowedMethods): bool
    {
        return $allowedMethods === CorsMetadata::ALLOWED_REQUEST_METHODS;
    }
}
