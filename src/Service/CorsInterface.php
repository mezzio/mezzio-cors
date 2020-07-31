<?php

declare(strict_types=1);

namespace Mezzio\Cors\Service;

use Psr\Http\Message\ServerRequestInterface;

interface CorsInterface
{
    /**
     * Creates the cors metadata from
     */
    public function metadata(ServerRequestInterface $request): CorsMetadata;

    /**
     * Should detect if a request is a request which needs CORS informations.
     */
    public function isCorsRequest(ServerRequestInterface $request): bool;

    /**
     * Should detect if a request is a preflight request.
     */
    public function isPreflightRequest(ServerRequestInterface $request): bool;
}
