<?php

declare(strict_types=1);

namespace Mezzio\Cors\Service;

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Psr\Http\Message\ResponseFactoryInterface as PsrResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

use function implode;
use function sprintf;

final class ResponseFactory implements ResponseFactoryInterface
{
    private PsrResponseFactoryInterface $responseFactory;

    public function __construct(PsrResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function preflight(string $origin, ConfigurationInterface $config): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(204, 'CORS Details')
            ->withAddedHeader('Content-Length', '0')
            ->withAddedHeader('Access-Control-Allow-Origin', $origin)
            ->withAddedHeader('Access-Control-Allow-Methods', implode(', ', $config->allowedMethods()))
            ->withAddedHeader('Access-Control-Allow-Headers', implode(', ', $config->allowedHeaders()))
            ->withAddedHeader('Access-Control-Max-Age', $config->allowedMaxAge());

        if (! $config->credentialsAllowed()) {
            return $response;
        }

        return $response->withAddedHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function cors(
        ResponseInterface $response,
        string $origin,
        ConfigurationInterface $config
    ): ResponseInterface {
        $response = $response
            ->withAddedHeader('Access-Control-Allow-Origin', $origin)
            ->withAddedHeader('Access-Control-Expose-Headers', implode(', ', $config->exposedHeaders()));

        if (! $config->credentialsAllowed()) {
            return $response;
        }

        return $response->withAddedHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function unauthorized(string $origin): ResponseInterface
    {
        return $this->responseFactory->createResponse(403, sprintf('The origin "%s" is not authorized', $origin));
    }
}
