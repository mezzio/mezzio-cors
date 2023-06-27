<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Service;

use Laminas\Diactoros\Response\TextResponse;
use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Service\ResponseFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

use function implode;

final class ResponseFactoryTest extends TestCase
{
    private ResponseFactory $responseFactory;
    private ResponseFactoryInterface&MockObject $psrResponseFactory;

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

        $response = new TextResponse('');

        $this->psrResponseFactory
            ->expects(self::once())
            ->method('createResponse')
            ->with(204, 'CORS Details')
            ->willReturn($response);

        $methods = ['GET', 'POST', 'DELETE'];
        $headers = ['X-Foo-Bar', 'X-Bar-Baz'];

        $configuration
            ->expects(self::once())
            ->method('allowedMethods')
            ->willReturn($methods);

        $configuration
            ->expects(self::once())
            ->method('allowedHeaders')
            ->willReturn($headers);

        $configuration
            ->expects(self::once())
            ->method('allowedMaxAge')
            ->willReturn('0');

        $configuration
            ->expects(self::once())
            ->method('credentialsAllowed')
            ->willReturn(false);

        $responseFromFactory = $this->responseFactory->preflight($origin, $configuration);

        self::assertNotSame($response, $responseFromFactory);
        self::assertEquals('0', $responseFromFactory->getHeader('Content-Length')[0] ?? null);
        self::assertEquals($origin, $responseFromFactory->getHeader('Access-Control-Allow-Origin')[0] ?? null);
        $expectAllowMethods = implode(', ', $methods);
        self::assertEquals(
            $expectAllowMethods,
            $responseFromFactory->getHeader('Access-Control-Allow-Methods')[0] ?? null,
        );

        $expectAllowHeaders = implode(', ', $headers);
        self::assertEquals(
            $expectAllowHeaders,
            $responseFromFactory->getHeader('Access-Control-Allow-Headers')[0] ?? null,
        );
        self::assertEquals(
            '0',
            $responseFromFactory->getHeader('Access-Control-Max-Age')[0] ?? null,
        );
    }

    public function testWillApplyExpectedHeadersToPreflightResponseWithCredentialsAllowed(): void
    {
        $origin        = 'http://www.example.org';
        $configuration = $this->createMock(ConfigurationInterface::class);

        $response = new TextResponse('');

        $this->psrResponseFactory
            ->expects(self::once())
            ->method('createResponse')
            ->with(204, 'CORS Details')
            ->willReturn($response);

        $methods = ['GET'];
        $headers = ['X-Foo-Bar'];

        $configuration
            ->expects(self::once())
            ->method('allowedMethods')
            ->willReturn($methods);

        $configuration
            ->expects(self::once())
            ->method('allowedHeaders')
            ->willReturn($headers);

        $configuration
            ->expects(self::once())
            ->method('allowedMaxAge')
            ->willReturn('0');

        $configuration
            ->expects(self::once())
            ->method('credentialsAllowed')
            ->willReturn(true);

        $responseFromFactory = $this->responseFactory->preflight($origin, $configuration);

        self::assertNotSame($response, $responseFromFactory);
        self::assertEquals('0', $responseFromFactory->getHeader('Content-Length')[0] ?? null);
        self::assertEquals($origin, $responseFromFactory->getHeader('Access-Control-Allow-Origin')[0] ?? null);
        $expectAllowMethods = implode(', ', $methods);
        self::assertEquals(
            $expectAllowMethods,
            $responseFromFactory->getHeader('Access-Control-Allow-Methods')[0] ?? null,
        );

        $expectAllowHeaders = implode(', ', $headers);
        self::assertEquals(
            $expectAllowHeaders,
            $responseFromFactory->getHeader('Access-Control-Allow-Headers')[0] ?? null,
        );
        self::assertEquals(
            '0',
            $responseFromFactory->getHeader('Access-Control-Max-Age')[0] ?? null,
        );
        self::assertEquals(
            'true',
            $responseFromFactory->getHeader('Access-Control-Allow-Credentials')[0] ?? null,
        );
    }

    public function testWillCreateUnauthorizedResponse(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $this->psrResponseFactory
            ->expects(self::once())
            ->method('createResponse')
            ->with(403)
            ->willReturn($response);

        $origin = 'foo';

        $responseFromFactory = $this->responseFactory->unauthorized($origin);
        self::assertEquals($response, $responseFromFactory);
    }

    public function testWillApplyExpectedHeadersToCorsResponseWithoutCredentialsAllowed(): void
    {
        $origin        = 'http://www.example.org';
        $configuration = $this->createMock(ConfigurationInterface::class);

        $response = new TextResponse('Hey There');

        $this->psrResponseFactory
            ->expects(self::never())
            ->method(self::anything());

        $headers = ['X-Bar'];

        $configuration
            ->expects(self::once())
            ->method('exposedHeaders')
            ->willReturn($headers);

        $configuration
            ->expects(self::once())
            ->method('credentialsAllowed')
            ->willReturn(false);

        $responseFromFactory = $this->responseFactory->cors($response, $origin, $configuration);

        self::assertNotSame($response, $responseFromFactory);
        self::assertEquals($origin, $responseFromFactory->getHeader('Access-Control-Allow-Origin')[0] ?? null);
        $expectExposeHeaders = implode(', ', $headers);
        self::assertEquals(
            $expectExposeHeaders,
            $responseFromFactory->getHeader('Access-Control-Expose-Headers')[0] ?? null,
        );
    }

    public function testWillApplyExpectedHeadersToCorsResponseWithCredentialsAllowed(): void
    {
        $origin        = 'http://www.example.org';
        $configuration = $this->createMock(ConfigurationInterface::class);

        $response = new TextResponse('Hey There');

        $this->psrResponseFactory
            ->expects(self::never())
            ->method(self::anything());

        $headers = ['X-Bar', 'X-Baz'];

        $configuration
            ->expects(self::once())
            ->method('exposedHeaders')
            ->willReturn($headers);

        $configuration
            ->expects(self::once())
            ->method('credentialsAllowed')
            ->willReturn(true);

        $responseFromFactory = $this->responseFactory->cors($response, $origin, $configuration);

        self::assertNotSame($response, $responseFromFactory);
        self::assertEquals($origin, $responseFromFactory->getHeader('Access-Control-Allow-Origin')[0] ?? null);
        $expectExposeHeaders = implode(', ', $headers);
        self::assertEquals(
            $expectExposeHeaders,
            $responseFromFactory->getHeader('Access-Control-Expose-Headers')[0] ?? null,
        );
        self::assertEquals(
            'true',
            $responseFromFactory->getHeader('Access-Control-Allow-Credentials')[0] ?? null,
        );
    }
}
