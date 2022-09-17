<?php

declare(strict_types=1);

namespace Mezzio\Cors\Service;

use Fig\Http\Message\RequestMethodInterface;
use InvalidArgumentException;
use Mezzio\Cors\Exception\InvalidOriginValueException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Webmozart\Assert\Assert;

use function strtoupper;
use function trim;

final class Cors implements CorsInterface
{
    private UriFactoryInterface $uriFactory;

    public function __construct(UriFactoryInterface $uriFactory)
    {
        $this->uriFactory = $uriFactory;
    }

    public function isPreflightRequest(ServerRequestInterface $request): bool
    {
        return $this->isCorsRequest($request)
            && strtoupper($request->getMethod()) === RequestMethodInterface::METHOD_OPTIONS
            && $request->hasHeader('Access-Control-Request-Method');
    }

    public function isCorsRequest(ServerRequestInterface $request): bool
    {
        $origin = $this->origin($request);
        if (! $origin instanceof UriInterface) {
            return false;
        }

        $uri = $request->getUri();

        return $uri->getScheme() !== $origin->getScheme()
            || $uri->getPort() !== $origin->getPort()
            || $uri->getHost() !== $origin->getHost();
    }

    private function origin(ServerRequestInterface $request): ?UriInterface
    {
        $origin = $request->getHeaderLine('Origin');

        if (trim($origin) === '') {
            return null;
        }

        try {
            return $this->uriFactory->createUri($origin);
        } catch (InvalidArgumentException $exception) {
            throw InvalidOriginValueException::fromThrowable($origin, $exception);
        }
    }

    public function metadata(ServerRequestInterface $request): CorsMetadata
    {
        $origin = $this->origin($request);
        Assert::isInstanceOf($origin, UriInterface::class);

        return new CorsMetadata(
            $origin,
            $request->getUri(),
            strtoupper($request->getHeaderLine('Access-Control-Request-Method') ?: $request->getMethod())
        );
    }
}
