<?php

declare(strict_types=1);

namespace Mezzio\Cors\Service;

use Mezzio\Cors\Configuration\ConfigurationInterface;
use Mezzio\Cors\Configuration\Exception\InvalidConfigurationException;

interface ConfigurationLocatorInterface
{
    /**
     * Should locate the configuration we have to apply to the response.
     *
     * @throws InvalidConfigurationException If there are more than one routes matching the request uri of
     *                                       the provided CorsMetadata.
     */
    public function locate(CorsMetadata $metadata): ?ConfigurationInterface;
}
