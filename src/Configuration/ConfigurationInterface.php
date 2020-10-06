<?php

declare(strict_types=1);

namespace Mezzio\Cors\Configuration;

interface ConfigurationInterface
{
    /**
     * Identifier to locate the project specific CORS configuration in the projects configuration.
     */
    public const CONFIGURATION_IDENTIFIER = 'expressive.cors';
    public const ANY_ORIGIN               = '*';
    public const PREFLIGHT_CACHE_DISABLED = '-1';

    /**
     * Should return all allowed methods, the requested path can handle.
     *
     * @psalm-return list<string>
     */
    public function allowedMethods(): array;

    /**
     * Should return all allowed headers, the request path can handle.
     *
     * @psalm-return list<string>
     */
    public function allowedHeaders(): array;

    /**
     * Should return the maximum age, the preflight response may be cached by a client.
     */
    public function allowedMaxAge(): string;

    /**
     * Should return all headers which are being exposed of the endpoint.
     *
     * @psalm-return list<string>
     */
    public function exposedHeaders(): array;

    /**
     * If a request is allowed to pass cookies, this method should return true.
     */
    public function credentialsAllowed(): bool;

    /**
     * @psalm-return list<string>
     */
    public function allowedOrigins(): array;
}
