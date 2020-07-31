<?php

declare(strict_types=1);

namespace Mezzio\Cors\Service;

use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use Mezzio\Cors\Configuration\ConfigurationInterface;
use Psr\Http\Message\UriInterface;
use Webmozart\Assert\Assert;

use function fnmatch;
use function in_array;

class CorsMetadata
{
    public const ALLOWED_REQUEST_METHODS = [
        RequestMethod::METHOD_DELETE,
        RequestMethod::METHOD_GET,
        RequestMethod::METHOD_HEAD,
        RequestMethod::METHOD_OPTIONS,
        RequestMethod::METHOD_PATCH,
        RequestMethod::METHOD_POST,
        RequestMethod::METHOD_PUT,
        RequestMethod::METHOD_TRACE,
    ];

    public const UNAUTHORIZED_ORIGIN = 'null';

    /** @var UriInterface */
    public $origin;

    /** @var UriInterface */
    public $requestedUri;

    /** @var string */
    public $requestedMethod;

    public function __construct(UriInterface $origin, UriInterface $requestedUri, string $requestMethod)
    {
        Assert::oneOf($requestMethod, self::ALLOWED_REQUEST_METHODS);
        $this->origin          = $origin;
        $this->requestedUri    = $requestedUri;
        $this->requestedMethod = $requestMethod;
    }

    public function origin(ConfigurationInterface $configuration): string
    {
        $allowed = $configuration->allowedOrigins();
        $origin  = (string) $this->origin;

        if (in_array(ConfigurationInterface::ANY_ORIGIN, $allowed, true)) {
            return $origin;
        }

        foreach ($allowed as $pattern) {
            if (fnmatch($pattern, $origin)) {
                return $origin;
            }
        }

        return self::UNAUTHORIZED_ORIGIN;
    }
}
