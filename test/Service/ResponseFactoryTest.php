<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Service;

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Service\ResponseFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

use function implode;

final class ResponseFactoryTest extends TestCase
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @psalm-var MockObject&ResponseFactoryInterface
     */
    private $psrResponseFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->psrResponseFactory = $this->createMock(ResponseFactoryInterface::class);
        $this->responseFactory    = new ResponseFactory($this->psrResponseFactory);
    }

    public function testWillApplyExpectedHeadersToPreflightResponseWithoutCredentialsAllowed(): void
    {
        $origin        = 'http://www.example.org';
        $configuration = $this->createMock(ConfigurationInterface::class);

        $response = $this->createMock(ResponseInterface::class);

        $this->psrResponseFactory
            ->expects($this->once())
            ->method('createResponse')
            ->with(204, 'CORS Details')
            ->willReturn($response);

        $methods = ['GET', 'POST', 'DELETE'];
        $headers = ['X-Foo-Bar', 'X-Bar-Baz'];

        $configuration
            ->expects($this->once())
            ->method('allowedMethods')
            ->willReturn($methods);

        $configuration
            ->expects($this->once())
            ->method('allowedHeaders')
            ->willReturn($headers);

        $configuration
            ->expects($this->once())
            ->method('allowedMaxAge')
            ->willReturn('0');

        $configuration
            ->expects($this->once())
            ->method('credentialsAllowed')
            ->willReturn(false);

        $response
            ->expects($this->any())
            ->method('withAddedHeader')
            ->withConsecutive(
                ['Content-Length', 0],
                ['Access-Control-Allow-Origin', $origin],
                ['Access-Control-Allow-Methods', implode(', ', $methods)],
                ['Access-Control-Allow-Headers', implode(', ', $headers)],
                ['Access-Control-Max-Age', 0],
            )
            ->willReturnSelf();

        $responseFromFactory = $this->responseFactory->preflight($origin, $configuration);
        $this->assertEquals($response, $responseFromFactory);
    }

    public function testWillApplyExpectedHeadersToPreflightResponseWithCredentialsAllowed(): void
    {
        $origin        = 'http://www.example.org';
        $configuration = $this->createMock(ConfigurationInterface::class);

        $response = $this->createMock(ResponseInterface::class);

        $this->psrResponseFactory
            ->expects($this->once())
            ->method('createResponse')
            ->with(204, 'CORS Details')
            ->willReturn($response);

        $methods = ['GET'];
        $headers = ['X-Foo-Bar'];

        $configuration
            ->expects($this->once())
            ->method('allowedMethods')
            ->willReturn($methods);

        $configuration
            ->expects($this->once())
            ->method('allowedHeaders')
            ->willReturn($headers);

        $configuration
            ->expects($this->once())
            ->method('allowedMaxAge')
            ->willReturn('0');

        $configuration
            ->expects($this->once())
            ->method('credentialsAllowed')
            ->willReturn(true);

        $response
            ->expects($this->any())
            ->method('withAddedHeader')
            ->withConsecutive(
                ['Content-Length', 0],
                ['Access-Control-Allow-Origin', $origin],
                ['Access-Control-Allow-Methods', implode(', ', $methods)],
                ['Access-Control-Allow-Headers', implode(', ', $headers)],
                ['Access-Control-Max-Age', 0],
                ['Access-Control-Allow-Credentials', 'true'],
            )
            ->willReturnSelf();

        $responseFromFactory = $this->responseFactory->preflight($origin, $configuration);

        $this->assertEquals($response, $responseFromFactory);
    }

    public function testWillCreateUnauthorizedResponse(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $this->psrResponseFactory
            ->expects($this->once())
            ->method('createResponse')
            ->with(403)
            ->willReturn($response);

        $origin = 'foo';

        $responseFromFactory = $this->responseFactory->unauthorized($origin);
        $this->assertEquals($response, $responseFromFactory);
    }

    public function testWillApplyExpectedHeadersToCorsResponseWithoutCredentialsAllowed(): void
    {
        $origin        = 'http://www.example.org';
        $configuration = $this->createMock(ConfigurationInterface::class);

        $response = $this->createMock(ResponseInterface::class);

        $this->psrResponseFactory
            ->expects($this->never())
            ->method($this->anything());

        $headers = ['X-Bar'];

        $configuration
            ->expects($this->once())
            ->method('exposedHeaders')
            ->willReturn($headers);

        $configuration
            ->expects($this->once())
            ->method('credentialsAllowed')
            ->willReturn(false);

        $response
            ->expects($this->any())
            ->method('withAddedHeader')
            ->withConsecutive(
                ['Access-Control-Allow-Origin', $origin],
                ['Access-Control-Expose-Headers', implode(', ', $headers)]
            )
            ->willReturnSelf();

        $responseFromFactory = $this->responseFactory->cors($response, $origin, $configuration);
        $this->assertEquals($response, $responseFromFactory);
    }

    public function testWillApplyExpectedHeadersToCorsResponseWithCredentialsAllowed(): void
    {
        $origin        = 'http://www.example.org';
        $configuration = $this->createMock(ConfigurationInterface::class);

        $response = $this->createMock(ResponseInterface::class);

        $this->psrResponseFactory
            ->expects($this->never())
            ->method($this->anything());

        $headers = ['X-Bar', 'X-Baz'];

        $configuration
            ->expects($this->once())
            ->method('exposedHeaders')
            ->willReturn($headers);

        $configuration
            ->expects($this->once())
            ->method('credentialsAllowed')
            ->willReturn(true);

        $response
            ->expects($this->any())
            ->method('withAddedHeader')
            ->withConsecutive(
                ['Access-Control-Allow-Origin', $origin],
                ['Access-Control-Expose-Headers', implode(', ', $headers)],
                ['Access-Control-Allow-Credentials', 'true']
            )
            ->willReturnSelf();

        $responseFromFactory = $this->responseFactory->cors($response, $origin, $configuration);
        $this->assertEquals($response, $responseFromFactory);
    }
}
