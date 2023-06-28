<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Service;

use Generator;
use InvalidArgumentException;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use Mezzio\Cors\Exception\InvalidOriginValueException;
use Mezzio\Cors\Service\Cors;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

use function sprintf;

final class CorsTest extends TestCase
{
    private UriFactoryInterface&MockObject $uriFactory;
    private Cors $cors;

    /**
     * @dataProvider crossOriginProvider
     */
    public function testWillDetectIfARequestIsACrossOriginRequest(string $scheme, string $host, ?int $port = null): void
    {
        $requestUri = $this->createMock(UriInterface::class);
        $this->applyUriInterfaceMethodAssertions($requestUri, $scheme, sprintf('subdomain.%s', $host), $port);

        $origin = sprintf('%s://%s', $scheme, $host);
        if ($port !== null) {
            $origin = sprintf('%s:%d', $origin, $port);
        }

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getUri')
            ->willReturn($requestUri);

        $request
            ->expects(self::once())
            ->method('getHeaderLine')
            ->with('Origin')
            ->willReturn($origin);

        $originUri = $this->createMock(UriInterface::class);
        $this->applyUriInterfaceMethodAssertions($originUri, $scheme, $host, $port);

        $this->uriFactory
            ->expects(self::once())
            ->method('createUri')
            ->with($origin)
            ->willReturn($originUri);

        self::assertTrue($this->cors->isCorsRequest($request));
    }

    /**
     * @psalm-param MockObject&UriInterface $uri
     */
    private function applyUriInterfaceMethodAssertions(
        MockObject $uri,
        string $scheme,
        string $host,
        ?int $port
    ): void {
        $uri
            ->expects(self::once())
            ->method('getScheme')
            ->willReturn($scheme);

        $uri
            ->expects(self::once())
            ->method('getHost')
            ->willReturn($host);

        $uri
            ->expects(self::once())
            ->method('getPort')
            ->willReturn($port);
    }

    // phpcs:disable Generic.Files.LineLength.TooLong

    public function testWontDetectRequestAsCrossOriginIfNoOriginHeaderIsPresent(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getHeaderLine')
            ->with('Origin')
            ->willReturn('');

        self::assertFalse($this->cors->isCorsRequest($request));
    }

    // phpcs:disable Generic.Files.LineLength.TooLong

    public function testWillThrowInvalidOriginValueExceptionIfOriginContainsValueWhichCannotBeParsedOnIsCorsRequestMethodCall(): void
    {
        $this->expectException(InvalidOriginValueException::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getHeaderLine')
            ->willReturn('foo');

        $this->uriFactory
            ->expects(self::once())
            ->method('createUri')
            ->with('foo')
            ->willThrowException(new InvalidArgumentException('Whatever'));

        $this->cors->isCorsRequest($request);
    }

    public function testWillThrowInvalidOriginValueExceptionIfOriginContainsValueWhichCannotBeParsedOnIsPreflightRequestMethodCall(): void
    {
        $this->expectException(InvalidOriginValueException::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getHeaderLine')
            ->willReturn('foo');

        $this->uriFactory
            ->expects(self::once())
            ->method('createUri')
            ->with('foo')
            ->willThrowException(new InvalidArgumentException('Whatever'));

        $this->cors->isPreflightRequest($request);
    }

    /**
     * @psalm-return Generator<non-empty-string,array{0:non-empty-string,1:non-empty-string,2?:int}>
     */
    public static function crossOriginProvider(): Generator
    {
        yield 'secured' => [
            'https',
            'example.org',
        ];
        yield 'non secured' => [
            'http',
            'example.org',
        ];
        yield'custom scheme' => [
            'android',
            'example.org',
        ];
        yield 'request with port' => [
            'https',
            'example.org',
            8080,
        ];
    }

    public function testWillDetectPreflightRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $requestUri = $this->createMock(UriInterface::class);
        $originUri  = $this->createMock(UriInterface::class);
        $this->applyUriInterfaceMethodAssertions($requestUri, 'http', 'foo', null);
        $this->applyUriInterfaceMethodAssertions($originUri, 'http', 'bar', null);

        $request
            ->expects(self::once())
            ->method('getHeaderLine')
            ->willReturn('baz');

        $this->uriFactory
            ->expects(self::once())
            ->method('createUri')
            ->with('baz')
            ->willReturn($originUri);

        $request
            ->expects(self::once())
            ->method('getUri')
            ->willReturn($requestUri);

        $request
            ->expects(self::once())
            ->method('getMethod')
            ->willReturn('options');

        $request
            ->expects(self::once())
            ->method('hasHeader')
            ->with('Access-Control-Request-Method')
            ->willReturn(true);

        self::assertTrue($this->cors->isPreflightRequest($request));
    }

    public function testWillCreateMetadataForPreflightRequest(): void
    {
        $requestUri = new Uri('https://request.example.com');
        $originUri  = new Uri('https://origin.example.com');

        $request = (new ServerRequest())
            ->withUri($requestUri)
            ->withAddedHeader('Origin', 'foo')
            ->withAddedHeader('Access-Control-Request-Method', 'GET');

        $this->uriFactory
            ->expects(self::once())
            ->method('createUri')
            ->with('foo')
            ->willReturn($originUri);

        $metadata = $this->cors->metadata($request);

        self::assertEquals($originUri, $metadata->origin);
        self::assertEquals($requestUri, $metadata->requestedUri);
        self::assertEquals('GET', $metadata->requestedMethod);
    }

    public function testWillCreateMetadataForCorsRequest(): void
    {
        $requestUri = new Uri('https://request.example.com');
        $originUri  = new Uri('https://origin.example.com');

        $request = (new ServerRequest())
            ->withUri($requestUri)
            ->withMethod('GET')
            ->withAddedHeader('Origin', 'foo')
            ->withAddedHeader('Access-Control-Request-Method', '');

        $this->uriFactory
            ->expects(self::once())
            ->method('createUri')
            ->with('foo')
            ->willReturn($originUri);

        $metadata = $this->cors->metadata($request);

        self::assertEquals($originUri, $metadata->origin);
        self::assertEquals($requestUri, $metadata->requestedUri);
        self::assertEquals('GET', $metadata->requestedMethod);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->uriFactory = $this->createMock(UriFactoryInterface::class);
        $this->cors       = new Cors($this->uriFactory);
    }
}
