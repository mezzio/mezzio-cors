<?php

declare(strict_types=1);

namespace Mezzio\CorsTest\Service;

use Generator;
use InvalidArgumentException;
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
    /**
     * @psalm-var MockObject&UriFactoryInterface
     */
    private $uriFactory;

    /**
     * @var Cors
     */
    private $cors;

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
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($requestUri);

        $request
            ->expects($this->once())
            ->method('getHeaderLine')
            ->with('Origin')
            ->willReturn($origin);

        $originUri = $this->createMock(UriInterface::class);
        $this->applyUriInterfaceMethodAssertions($originUri, $scheme, $host, $port);

        $this->uriFactory
            ->expects($this->once())
            ->method('createUri')
            ->with($origin)
            ->willReturn($originUri);

        $this->assertTrue($this->cors->isCorsRequest($request));
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
            ->expects($this->once())
            ->method('getScheme')
            ->willReturn($scheme);

        $uri
            ->expects($this->once())
            ->method('getHost')
            ->willReturn($host);

        $uri
            ->expects($this->once())
            ->method('getPort')
            ->willReturn($port);
    }

    // phpcs:disable Generic.Files.LineLength.TooLong

    public function testWontDetectRequestAsCrossOriginIfNoOriginHeaderIsPresent(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getHeaderLine')
            ->with('Origin')
            ->willReturn('');

        $this->assertFalse($this->cors->isCorsRequest($request));
    }

    // phpcs:disable Generic.Files.LineLength.TooLong

    public function testWillThrowInvalidOriginValueExceptionIfOriginContainsValueWhichCannotBeParsedOnIsCorsRequestMethodCall(): void
    {
        $this->expectException(InvalidOriginValueException::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getHeaderLine')
            ->willReturn('foo');

        $this->uriFactory
            ->expects($this->once())
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
            ->expects($this->once())
            ->method('getHeaderLine')
            ->willReturn('foo');

        $this->uriFactory
            ->expects($this->once())
            ->method('createUri')
            ->with('foo')
            ->willThrowException(new InvalidArgumentException('Whatever'));

        $this->cors->isPreflightRequest($request);
    }

    /**
     * @psalm-return Generator<string,array<int,string|int>>
     */
    public function crossOriginProvider(): Generator
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
            ->expects($this->once())
            ->method('getHeaderLine')
            ->willReturn('baz');

        $this->uriFactory
            ->expects($this->once())
            ->method('createUri')
            ->with('baz')
            ->willReturn($originUri);

        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($requestUri);

        $request
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn('options');

        $request
            ->expects($this->once())
            ->method('hasHeader')
            ->with('Access-Control-Request-Method')
            ->willReturn(true);

        $this->assertTrue($this->cors->isPreflightRequest($request));
    }

    public function testWillCreateMetadataForPreflightRequest(): void
    {
        $requestUri = $this->createMock(UriInterface::class);
        $requestUri
            ->expects($this->never())
            ->method($this->anything());
        $originUri = $this->createMock(UriInterface::class);
        $originUri
            ->expects($this->never())
            ->method($this->anything());

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->any())
            ->method('getHeaderLine')
            ->withConsecutive(['Origin'], ['Access-Control-Request-Method'])
            ->willReturnOnConsecutiveCalls('foo', 'get');

        $this->uriFactory
            ->expects($this->once())
            ->method('createUri')
            ->with('foo')
            ->willReturn($originUri);

        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($requestUri);

        $metadata = $this->cors->metadata($request);

        $this->assertEquals($originUri, $metadata->origin);
        $this->assertEquals($requestUri, $metadata->requestedUri);
        $this->assertEquals('GET', $metadata->requestedMethod);
    }

    public function testWillCreateMetadataForCorsRequest(): void
    {
        $requestUri = $this->createMock(UriInterface::class);
        $requestUri
            ->expects($this->never())
            ->method($this->anything());
        $originUri = $this->createMock(UriInterface::class);
        $originUri
            ->expects($this->never())
            ->method($this->anything());

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->any())
            ->method('getHeaderLine')
            ->withConsecutive(['Origin'], ['Access-Control-Request-Method'])
            ->willReturnOnConsecutiveCalls('foo', '');

        $request
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn('get');

        $this->uriFactory
            ->expects($this->once())
            ->method('createUri')
            ->with('foo')
            ->willReturn($originUri);

        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($requestUri);

        $metadata = $this->cors->metadata($request);

        $this->assertEquals($originUri, $metadata->origin);
        $this->assertEquals($requestUri, $metadata->requestedUri);
        $this->assertEquals('GET', $metadata->requestedMethod);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->uriFactory = $this->createMock(UriFactoryInterface::class);
        $this->cors       = new Cors($this->uriFactory);
    }
}
