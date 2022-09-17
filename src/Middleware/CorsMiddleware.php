<?php

declare(strict_types=1);

namespace Mezzio\Cors\Middleware;

use Mezzio\Cors\Middleware\Exception\InvalidConfigurationException;
use Mezzio\Cors\Service\ConfigurationLocatorInterface;
use Mezzio\Cors\Service\CorsInterface;
use Mezzio\Cors\Service\CorsMetadata;
use Mezzio\Cors\Service\ResponseFactoryInterface;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function preg_match;

final class CorsMiddleware implements MiddlewareInterface
{
    private CorsInterface $cors;

    private ConfigurationLocatorInterface $configurationLocator;

    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        CorsInterface $cors,
        ConfigurationLocatorInterface $configurationLocator,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->cors                 = $cors;
        $this->configurationLocator = $configurationLocator;
        $this->responseFactory      = $responseFactory;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var null|RouteResult $route */
        $route = $request->getAttribute(RouteResult::class);
        if ($route) {
            throw InvalidConfigurationException::fromInvalidPipelineConfiguration();
        }

        if (! $this->cors->isCorsRequest($request)) {
            return $this->vary($handler->handle($request));
        }

        $metadata = $this->cors->metadata($request);
        if ($this->cors->isPreflightRequest($request)) {
            return $this->preflight($metadata) ?? $handler->handle($request);
        }

        return $this->cors($metadata, $request, $handler);
    }

    private function vary(ResponseInterface $response): ResponseInterface
    {
        if (! $response->hasHeader('Vary')) {
            return $response->withAddedHeader('Vary', 'Origin');
        }

        $vary = $response->getHeaderLine('Vary');
        if (preg_match('#(^|,\s?)Origin($|,\s?)#', $vary)) {
            return $response;
        }

        return $response->withHeader('Vary', $vary . ', Origin');
    }

    private function preflight(CorsMetadata $metadata): ?ResponseInterface
    {
        $configurationToApply = $this->configurationLocator->locate($metadata);
        if (! $configurationToApply) {
            return null;
        }

        return $this->responseFactory->preflight(
            $metadata->origin($configurationToApply),
            $configurationToApply
        );
    }

    private function cors(
        CorsMetadata $metadata,
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $configurationToApply = $this->configurationLocator->locate($metadata);
        if (! $configurationToApply) {
            return $handler->handle($request);
        }

        $origin = $metadata->origin($configurationToApply);

        if ($origin === CorsMetadata::UNAUTHORIZED_ORIGIN) {
            return $this->responseFactory->unauthorized((string) $metadata->origin);
        }

        $response = $handler->handle($request);

        return $this->vary(
            $this->responseFactory->cors($response, $origin, $configurationToApply)
        );
    }
}
