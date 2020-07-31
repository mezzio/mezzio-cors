<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Middleware;

use Fig\Http\Message\RequestMethodInterface;
use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\RouteConfigurationInterface;
use Mezzio\Cors\Middleware\CorsMiddleware;
use Mezzio\Cors\Middleware\Exception\InvalidConfigurationException;
use Mezzio\Cors\Service\ConfigurationLocatorInterface;
use Mezzio\Cors\Service\CorsInterface;
use Mezzio\Cors\Service\CorsMetadata;
use Mezzio\Cors\Service\ResponseFactoryInterface;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CorsMiddlewareTest extends TestCase
{
    /** @psalm-var CorsInterface&MockObject */
    private $cors;

    /** @psalm-var ConfigurationLocatorInterface&MockObject */
    private $configurationLocator;

    /** @psalm-var ResponseFactoryInterface&MockObject */
    private $responseFactoryInterface;

    /** @var CorsMiddleware */
    private $middleware;

    /**
     * @return array<string,mixed>
     */
    public function varyHeaderProvider(): array
    {
        return [
            'just origin'                                => [
                'Origin',
            ],
            'origin at the end'                          => [
                'Accept, Origin',
            ],
            'origin at the beginning'                    => [
                'Origin, Accept',
            ],
            'origin in the middle'                       => [
                'Accept, Origin, User-Agent',
            ],
            'origin at the end without whitespace'       => [
                'Accept,Origin',
            ],
            'origin at the beginning without whitespace' => [
                'Origin,Accept',
            ],
            'origin in the middle without whitespaces'   => [
                'Accept,Origin,User-Agent',
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->cors                     = $this->createMock(CorsInterface::class);
        $this->configurationLocator     = $this->createMock(ConfigurationLocatorInterface::class);
        $this->responseFactoryInterface = $this->createMock(ResponseFactoryInterface::class);

        $this->middleware = new CorsMiddleware(
            $this->cors,
            $this->configurationLocator,
            $this->responseFactoryInterface
        );
    }

    public function testWillThrowExceptionOnInvalidConfiguration(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $routeResult = $this->createMock(RouteResult::class);
        $request     = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(RouteResult::class)
            ->willReturn($routeResult);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $this->middleware->process($request, $handler);
    }

    public function testWillIgnoreNonCorsRequests(): void
    {
        $request  = $this->createMock(ServerRequestInterface::class);
        $handler  = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createResponseMock();
        $handler
            ->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $this->cors
            ->expects($this->once())
            ->method('isCorsRequest')
            ->with($request)
            ->willReturn(false);

        $this->cors
            ->expects($this->never())
            ->method('metadata');

        $this->cors
            ->expects($this->never())
            ->method('isPreflightRequest');

        $responseFromMiddleware = $this->middleware->process($request, $handler);
        $this->assertEquals($response, $responseFromMiddleware);
    }

    /**
     * @return ResponseInterface&MockObject
     */
    private function createResponseMock(
        bool $varyHeaderExists = false,
        bool $varyHeaderContainsOrigin = false
    ): ResponseInterface {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('hasHeader')
            ->willReturn($varyHeaderExists);

        if (! $varyHeaderExists) {
            $response
                ->expects($this->once())
                ->method('withAddedHeader')
                ->with('Vary', 'Origin')
                ->willReturn($response);

            return $response;
        }

        $varyHeaderValue = 'Accept';

        if ($varyHeaderContainsOrigin) {
            $varyHeaderValue = 'Accept, Origin';
        }

        $response
            ->expects($this->once())
            ->method('getHeaderLine')
            ->willReturn($varyHeaderValue);

        if ($varyHeaderContainsOrigin) {
            return $response;
        }

        $response
            ->expects($this->once())
            ->method('withHeader')
            ->with('Vary', 'Accept, Origin')
            ->willReturn($response);

        return $response;
    }

    public function testWillHandlePreflightRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects($this->never())
            ->method($this->anything());

        $this->cors
            ->expects($this->once())
            ->method('isCorsRequest')
            ->with($request)
            ->willReturn(true);

        $originUriInterface  = $this->createMock(UriInterface::class);
        $requestUriInterface = $this->createMock(UriInterface::class);

        $metadata = new CorsMetadata(
            $originUriInterface,
            $requestUriInterface,
            RequestMethodInterface::METHOD_GET
        );

        $this->cors
            ->expects($this->once())
            ->method('metadata')
            ->with($request)
            ->willReturn($metadata);

        $this->cors
            ->expects($this->once())
            ->method('isPreflightRequest')
            ->with($request)
            ->willReturn(true);

        $configuration = $this->createMock(RouteConfigurationInterface::class);

        $response = $this->createMock(ResponseInterface::class);

        $origin = 'http://www.example.org';
        $originUriInterface
            ->expects($this->once())
            ->method('__toString')
            ->willReturn($origin);

        $configuration
            ->expects($this->once())
            ->method('allowedOrigins')
            ->willReturn([$origin]);

        $this->responseFactoryInterface
            ->expects($this->once())
            ->method('preflight')
            ->with($origin, $configuration)
            ->willReturn($response);

        $this->configurationLocator
            ->expects($this->once())
            ->method('locate')
            ->with($metadata)
            ->willReturn($configuration);

        $responseFromMiddleware = $this->middleware->process($request, $handler);

        $this->assertEquals($response, $responseFromMiddleware);
    }

    public function testWillHandleUnauthorizedCorsRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects($this->never())
            ->method($this->anything());

        $this->cors
            ->expects($this->once())
            ->method('isCorsRequest')
            ->with($request)
            ->willReturn(true);

        $originUriInterface  = $this->createMock(UriInterface::class);
        $requestUriInterface = $this->createMock(UriInterface::class);

        $origin = 'http://www.example.org';
        $originUriInterface
            ->expects($this->any())
            ->method('__toString')
            ->willReturn($origin);

        $metadata = new CorsMetadata(
            $originUriInterface,
            $requestUriInterface,
            RequestMethodInterface::METHOD_GET
        );

        $this->cors
            ->expects($this->once())
            ->method('metadata')
            ->with($request)
            ->willReturn($metadata);

        $this->cors
            ->expects($this->once())
            ->method('isPreflightRequest')
            ->with($request)
            ->willReturn(false);

        $configuration = $this->createMock(RouteConfigurationInterface::class);

        $response = $this->createMock(ResponseInterface::class);

        $this->responseFactoryInterface
            ->expects($this->never())
            ->method('cors');

        $this->configurationLocator
            ->expects($this->once())
            ->method('locate')
            ->with($metadata)
            ->willReturn($configuration);

        $this->responseFactoryInterface
            ->expects($this->never())
            ->method('preflight');

        $this->responseFactoryInterface
            ->expects($this->once())
            ->method('unauthorized')
            ->with($origin)
            ->willReturn($response);

        $responseFromMiddleware = $this->middleware->process($request, $handler);

        $this->assertEquals($response, $responseFromMiddleware);
    }

    public function testWillHandleCorsRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->cors
            ->expects($this->once())
            ->method('isCorsRequest')
            ->with($request)
            ->willReturn(true);

        $originUriInterface  = $this->createMock(UriInterface::class);
        $requestUriInterface = $this->createMock(UriInterface::class);

        $origin = 'http://www.example.org';

        $metadata = new CorsMetadata(
            $originUriInterface,
            $requestUriInterface,
            RequestMethodInterface::METHOD_GET
        );

        $this->cors
            ->expects($this->once())
            ->method('metadata')
            ->with($request)
            ->willReturn($metadata);

        $this->cors
            ->expects($this->once())
            ->method('isPreflightRequest')
            ->with($request)
            ->willReturn(false);

        $configuration = $this->createMock(RouteConfigurationInterface::class);
        $configuration
            ->expects($this->once())
            ->method('allowedOrigins')
            ->willReturn([ConfigurationInterface::ANY_ORIGIN]);

        $response = $this->createResponseMock(true);

        $originUriInterface
            ->expects($this->once())
            ->method('__toString')
            ->willReturn($origin);

        $this->responseFactoryInterface
            ->expects($this->once())
            ->method('cors')
            ->with($response, $origin, $configuration)
            ->willReturn($response);

        $this->configurationLocator
            ->expects($this->once())
            ->method('locate')
            ->with($metadata)
            ->willReturn($configuration);

        $this->responseFactoryInterface
            ->expects($this->never())
            ->method('preflight');

        $this->responseFactoryInterface
            ->expects($this->never())
            ->method('unauthorized');

        $handler
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $responseFromMiddleware = $this->middleware->process($request, $handler);

        $this->assertEquals($response, $responseFromMiddleware);
    }

    /**
     * @dataProvider varyHeaderProvider
     */
    public function testWillFindOriginInResponseHeaders(string $vary)
    {
        $request  = $this->createMock(ServerRequestInterface::class);
        $handler  = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $response
            ->expects($this->once())
            ->method('hasHeader')
            ->willReturn(true);

        $response
            ->expects($this->once())
            ->method('getHeaderLine')
            ->willReturn($vary);

        $handler
            ->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $this->cors
            ->expects($this->once())
            ->method('isCorsRequest')
            ->with($request)
            ->willReturn(false);

        $this->cors
            ->expects($this->never())
            ->method('metadata');

        $this->cors
            ->expects($this->never())
            ->method('isPreflightRequest');

        $responseFromMiddleware = $this->middleware->process($request, $handler);
        $this->assertEquals($response, $responseFromMiddleware);
    }

    public function testWillDelegateUnknownRouteForPreflightRequestToRequestHandler()
    {
        $request  = $this->createMock(ServerRequestInterface::class);
        $handler  = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $this->cors
            ->expects($this->once())
            ->method('isCorsRequest')
            ->willReturn(true);

        $this->cors
            ->expects($this->once())
            ->method('isPreflightRequest')
            ->willReturn(true);

        $this->configurationLocator
            ->expects($this->once())
            ->method('locate')
            ->willReturn(null);

        $this->middleware->process($request, $handler);
    }

    public function testWillDelegateUnknownRouteForRequestToRequestHandler()
    {
        $request  = $this->createMock(ServerRequestInterface::class);
        $handler  = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $this->cors
            ->expects($this->once())
            ->method('isCorsRequest')
            ->willReturn(true);

        $this->cors
            ->expects($this->once())
            ->method('isPreflightRequest')
            ->willReturn(false);

        $this->configurationLocator
            ->expects($this->once())
            ->method('locate')
            ->willReturn(null);

        $this->middleware->process($request, $handler);
    }
}
