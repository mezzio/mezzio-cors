<?php

declare(strict_types=1);

namespace Mezzio\Cors\Configuration;

use Mezzio\Cors\Configuration\Exception\InvalidConfigurationException;
use Mezzio\Cors\Exception\BadMethodCallException;
use Webmozart\Assert\Assert;

use function array_unique;
use function array_values;
use function call_user_func;
use function in_array;
use function is_callable;
use function lcfirst;
use function sprintf;
use function str_replace;
use function ucfirst;
use function ucwords;

abstract class AbstractConfiguration implements ConfigurationInterface
{
    /**
     * @psalm-var list<string>
     */
    protected $allowedOrigins = [];

    /**
     * @psalm-var list<string>
     */
    protected $allowedMethods = [];

    /**
     * @psalm-var list<string>
     */
    protected $allowedHeaders = [];

    /** @var string */
    protected $allowedMaxAge = ConfigurationInterface::PREFLIGHT_CACHE_DISABLED;

    /** @var bool */
    protected $credentialsAllowed = false;

    /**
     * @psalm-var list<string>
     */
    protected $exposedHeaders = [];

    /**
     * @psalm-param array<string,mixed> $parameters
     */
    public function __construct(array $parameters)
    {
        try {
            $this->exchangeArray($parameters);
        } catch (BadMethodCallException $exception) {
            throw InvalidConfigurationException::create($exception->getMessage());
        }
    }

    /**
     * @param array<string,mixed> $data
     */
    public function exchangeArray(array $data): self
    {
        $instance = clone $this;

        /** @psalm-suppress MixedAssignment */
        foreach ($data as $property => $value) {
            $property = lcfirst(str_replace('_', '', ucwords($property, '_')));
            $setter   = sprintf('set%s', ucfirst($property));
            $callable = [$this, $setter];
            if (! is_callable($callable)) {
                throw BadMethodCallException::fromMissingSetterMethod($property, $setter);
            }

            call_user_func($callable, $value);
        }

        return $instance;
    }

    /**
     * @psalm-param list<string> $origins
     */
    public function setAllowedOrigins(array $origins): void
    {
        Assert::allString($origins);

        $origins = array_values(array_unique($origins));

        if (in_array(ConfigurationInterface::ANY_ORIGIN, $origins, true)) {
            $origins = [ConfigurationInterface::ANY_ORIGIN];
        }

        $this->allowedOrigins = $origins;
    }

    public function allowedMethods(): array
    {
        return $this->allowedMethods;
    }

    /**
     * @psalm-param list<string> $headers
     */
    public function setAllowedHeaders(array $headers): void
    {
        Assert::allString($headers);
        $this->allowedHeaders = array_values(array_unique($headers));
    }

    public function allowedHeaders(): array
    {
        return $this->allowedHeaders;
    }

    public function setAllowedMaxAge(string $age): void
    {
        if ($age) {
            Assert::numeric($age);
        }

        $this->allowedMaxAge = $age;
    }

    public function allowedMaxAge(): string
    {
        return $this->allowedMaxAge;
    }

    /**
     * @psalm-param list<string> $headers
     */
    public function setExposedHeaders(array $headers): void
    {
        Assert::allString($headers);
        $this->exposedHeaders = array_values(array_unique($headers));
    }

    public function exposedHeaders(): array
    {
        return $this->exposedHeaders;
    }

    public function credentialsAllowed(): bool
    {
        return $this->credentialsAllowed;
    }

    public function setCredentialsAllowed(bool $credentialsAllowed): void
    {
        $this->credentialsAllowed = $credentialsAllowed;
    }

    public function allowedOrigins(): array
    {
        return $this->allowedOrigins;
    }
}
