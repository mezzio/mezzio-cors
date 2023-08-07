<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Service;

use Fig\Http\Message\RequestMethodInterface;
use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\RouteConfigurationFactoryInterface;
use Mezzio\Cors\Configuration\RouteConfigurationInterface;
use Mezzio\Cors\Service\ConfigurationLocator;
use Mezzio\Cors\Service\CorsMetadata;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

use function array_fill;
use function array_shift;
use function array_values;
use function count;
use function in_array;

final class ConfigurationLocatorTest extends TestCase
{
    private ConfigurationLocator $locator;
    private ConfigurationInterface&MockObject $projectConfiguration;
    private ServerRequestFactoryInterface&MockObject $requestFactory;
    private RouterInterface&MockObject $router;
    private RouteConfigurationFactoryInterface&MockObject $routeConfigurationFactory;

    public function testWontLocateAnyConfigurationIfRouteIsUnknown(): void
    {
        $originUri  = $this->createMock(UriInterface::class);
        $requestUri = $this->createMock(UriInterface::class);
        $metadata   = new CorsMetadata($originUri, $requestUri, RequestMethodInterface::METHOD_GET);

        $request = $this->createMock(ServerRequestInterface::class);
        $this->requestFactory
            ->expects(self::any())
            ->method('createServerRequest')
            ->willReturn($request);

        $routeResult = $this->createMock(RouteResult::class);
        $routeResult
            ->expects(self::any())
            ->method('isFailure')
            ->willReturn(true);

        $this->router
            ->expects(self::any())
            ->method('match')
            ->willReturn($routeResult);

        $routeConfiguration = $this->createMock(RouteConfigurationInterface::class);

        $this->routeConfigurationFactory
            ->expects(self::once())
            ->method('__invoke')
            ->with([])
            ->willReturn($routeConfiguration);

        $routeConfiguration
            ->expects(self::once())
            ->method('mergeWithConfiguration')
            ->with($this->projectConfiguration)
            ->willReturn($routeConfiguration);

        $located = $this->locator->locate($metadata);
        self::assertNull($located);
    }

    public function testWillLocateProjectConfigDueRoutesWithoutConfigWithMergedRouteResultsMethods(): void
    {
        $originUri  = $this->createMock(UriInterface::class);
        $requestUri = $this->createMock(UriInterface::class);
        $metadata   = new CorsMetadata($originUri, $requestUri, RequestMethodInterface::METHOD_GET);

        $request = $this->createMock(ServerRequestInterface::class);
        $this->requestFactory
            ->expects(self::any())
            ->method('createServerRequest')
            ->with(RequestMethodInterface::METHOD_GET, $requestUri)
            ->willReturn($request);

        $routeResult = $this->createMock(RouteResult::class);
        $routeResult
            ->expects(self::any())
            ->method('isFailure')
            ->willReturn(false);

        $this->router
            ->expects(self::any())
            ->method('match')
            ->willReturn($routeResult);

        $allowedMethods = [RequestMethodInterface::METHOD_GET];

        $routeResult
            ->expects(self::once())
            ->method('getAllowedMethods')
            ->willReturn($allowedMethods);

        $routeConfiguration = $this->createMock(RouteConfigurationInterface::class);

        $routeConfiguration
            ->expects(self::any())
            ->method('mergeWithConfiguration')
            ->with($this->projectConfiguration)
            ->willReturnSelf();

        $routeConfiguration
            ->expects(self::once())
            ->method('withRequestMethods')
            ->with($allowedMethods)
            ->willReturnSelf();

        $this->routeConfigurationFactory
            ->expects(self::any())
            ->method('__invoke')
            ->willReturn($routeConfiguration);

        $routeConfiguration
            ->expects(self::once())
            ->method('explicit')
            ->willReturn(true);

        $located = $this->locator->locate($metadata);
        self::assertEquals($routeConfiguration, $located);
    }

    public function testWillHandleRouteThatOverridesProjectConfiguration(): void
    {
        $originUri  = $this->createMock(UriInterface::class);
        $requestUri = $this->createMock(UriInterface::class);
        $method     = 'GET';

        $request = $this->createMock(ServerRequestInterface::class);

        $this->requestFactory
            ->expects(self::once())
            ->method('createServerRequest')
            ->with($method, $requestUri)
            ->willReturn($request);

        $metadata = new CorsMetadata($originUri, $requestUri, $method);

        $routeResult = $this->createMock(RouteResult::class);
        $routeResult
            ->expects(self::once())
            ->method('isFailure')
            ->willReturn(false);

        $routeResult
            ->expects(self::once())
            ->method('getMatchedParams')
            ->willReturn([RouteConfigurationInterface::PARAMETER_IDENTIFIER => []]);

        $routeResult
            ->expects(self::once())
            ->method('getAllowedMethods')
            ->willReturn([]);

        $routeConfigurationForProject = $this->createMock(RouteConfigurationInterface::class);
        $routeConfigurationForProject
            ->expects(self::once())
            ->method('mergeWithConfiguration')
            ->with($this->projectConfiguration)
            ->willReturnSelf();

        $routeConfiguration = $this->createMock(RouteConfigurationInterface::class);

        $routeConfiguration
            ->expects(self::once())
            ->method('overridesProjectConfiguration')
            ->willReturn(true);

        $routeConfiguration
            ->expects(self::never())
            ->method('mergeWithConfiguration');

        $routeConfiguration
            ->expects(self::once())
            ->method('withRequestMethods')
            ->willReturnSelf();

        $routeConfiguration
            ->expects(self::once())
            ->method('explicit')
            ->willReturn(true);

        $configurations = new class (
            $routeConfigurationForProject,
            $routeConfiguration
        ) {
            /** @var list<RouteConfigurationInterface>  */
            private array $configurations;
            public function __construct(RouteConfigurationInterface ...$configuration)
            {
                $this->configurations = array_values($configuration);
            }

            public function next(): RouteConfigurationInterface|false
            {
                return array_shift($this->configurations);
            }
        };

        $this->routeConfigurationFactory
            ->expects(self::any())
            ->method('__invoke')
            ->willReturnCallback([$configurations, 'next']);

        $this->router
            ->expects(self::once())
            ->method('match')
            ->with($request)
            ->willReturn($routeResult);

        $config = $this->locator->locate($metadata);
        self::assertEquals($routeConfiguration, $config);
    }

    public function testWillMergeFromMultipleMatchingRoutes(): void
    {
        $originUri  = $this->createMock(UriInterface::class);
        $requestUri = $this->createMock(UriInterface::class);

        $method   = 'GET';
        $metadata = new CorsMetadata($originUri, $requestUri, $method);
        $request  = $this->createMock(ServerRequestInterface::class);

        $this->requestFactory
            ->expects(self::any())
            ->method('createServerRequest')
            ->with(
                self::callback(static function (string $method): bool {
                    self::assertTrue(in_array($method, CorsMetadata::ALLOWED_REQUEST_METHODS, true));

                    return true;
                }),
                $requestUri,
            )
            ->willReturn($request);

        $matchingRouteResult = $this->createMock(RouteResult::class);
        $matchingRouteResult
            ->expects(self::any())
            ->method('isFailure')
            ->willReturn(false);

        $matchingRouteResult
            ->expects(self::once())
            ->method('getAllowedMethods')
            ->willReturn(['OPTIONS', 'HEAD']);

        $failedRouteResult = $this->createMock(RouteResult::class);
        $failedRouteResult
            ->expects(self::any())
            ->method('isFailure')
            ->willReturn(true);

        $routeConfigurationParameters         = [
            RouteConfigurationInterface::PARAMETER_IDENTIFIER => [],
        ];
        $matchingRouteResultWithConfiguration = $this->createMock(RouteResult::class);
        $matchingRouteResultWithConfiguration
            ->expects(self::any())
            ->method('isFailure')
            ->willReturn(false);

        $matchingRouteResultWithConfiguration
            ->expects(self::once())
            ->method('getAllowedMethods')
            ->willReturn(['POST']);

        $matchingRouteResultWithConfiguration
            ->expects(self::once())
            ->method('getMatchedParams')
            ->willReturn($routeConfigurationParameters);

        $routeMatches = new class (
            $failedRouteResult,
            $matchingRouteResult,
            $matchingRouteResultWithConfiguration,
            ...array_fill(0, count(CorsMetadata::ALLOWED_REQUEST_METHODS) - 3, $failedRouteResult)
        ) {
            /** @var list<RouteResult>  */
            private array $routeResults;
            public function __construct(RouteResult ...$results)
            {
                $this->routeResults = array_values($results);
            }

            public function next(): RouteResult|false
            {
                return array_shift($this->routeResults);
            }
        };

        $this->router
            ->expects(self::any())
            ->method('match')
            ->with($request)
            ->willReturnCallback([$routeMatches, 'next']);

        $routeConfiguration = $this->createMock(RouteConfigurationInterface::class);
        $routeConfiguration
            ->expects(self::any())
            ->method('withRequestMethods')
            ->with(self::callback(static function (array $argument): bool {
                $expectedArguments = [
                    ['OPTIONS', 'HEAD'],
                    ['POST'],
                ];
                self::assertContains($argument, $expectedArguments);

                return true;
            }))
            ->willReturnSelf();

        $routeConfiguration
            ->expects(self::any())
            ->method('mergeWithConfiguration')
            ->willReturnSelf();

        $this->routeConfigurationFactory
            ->expects(self::any())
            ->method('__invoke')
            ->willReturn($routeConfiguration);

        $locatedConfiguration = $this->locator->locate($metadata);
        self::assertSame($routeConfiguration, $locatedConfiguration);
    }

    public function testWillEarlyReturnExplicitRouteConfiguration(): void
    {
        $originUri  = $this->createMock(UriInterface::class);
        $requestUri = $this->createMock(UriInterface::class);

        $method   = 'GET';
        $metadata = new CorsMetadata($originUri, $requestUri, $method);

        $failedRouteResult = $this->createMock(RouteResult::class);
        $failedRouteResult
            ->expects(self::any())
            ->method('isFailure')
            ->willReturn(true);

        $matchingExplicitRouteResult = $this->createMock(RouteResult::class);
        $matchingExplicitRouteResult
            ->expects(self::any())
            ->method('isFailure')
            ->willReturn(false);

        $routeConfigurationParameters = [
            RouteConfigurationInterface::PARAMETER_IDENTIFIER => [
                'explicit' => true,
            ],
        ];

        $matchingExplicitRouteResult
            ->expects(self::once())
            ->method('getMatchedParams')
            ->willReturn($routeConfigurationParameters);

        $matchingExplicitRouteResult
            ->expects(self::once())
            ->method('getAllowedMethods')
            ->willReturn(['POST']);

        $routeConfiguration = $this->createMock(RouteConfigurationInterface::class);
        $routeConfiguration
            ->expects(self::any())
            ->method('mergeWithConfiguration')
            ->willReturnSelf();

        $routeConfiguration
            ->expects(self::once())
            ->method('withRequestMethods')
            ->with(['POST'])
            ->willReturnSelf();

        $this->routeConfigurationFactory
            ->expects(self::any())
            ->method('__invoke')
            ->with(self::isType('array'))
            ->willReturn($routeConfiguration);

        $routeConfiguration
            ->expects(self::any())
            ->method('explicit')
            ->willReturn(true);

        $routeMatches = new class (
            $failedRouteResult,
            $matchingExplicitRouteResult
        ) {
            /** @var list<RouteResult>  */
            private array $routeResults;
            public function __construct(RouteResult ...$results)
            {
                $this->routeResults = array_values($results);
            }

            public function next(): RouteResult|false
            {
                return array_shift($this->routeResults);
            }
        };

        $this
            ->router
            ->expects(self::any())
            ->method('match')
            ->willReturnCallback([$routeMatches, 'next']);

        $locatedConfiguration = $this->locator->locate($metadata);
        self::assertSame($routeConfiguration, $locatedConfiguration);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectConfiguration      = $this->createMock(ConfigurationInterface::class);
        $this->requestFactory            = $this->createMock(ServerRequestFactoryInterface::class);
        $this->router                    = $this->createMock(RouterInterface::class);
        $this->routeConfigurationFactory = $this->createMock(RouteConfigurationFactoryInterface::class);

        $this->locator = new ConfigurationLocator(
            $this->projectConfiguration,
            $this->requestFactory,
            $this->router,
            $this->routeConfigurationFactory
        );
    }
}
