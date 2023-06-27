<?php

declare(strict_types=1);

namespace Mezzio\CorsTest;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

abstract class AbstractFactoryTestCase extends TestCase
{
    private InMemoryContainer $container;

    /**
     * @return array<string ,mixed>
     */
    abstract protected function dependencies(): array;

    /**
     * @psalm-return callable(ContainerInterface $container):object
     */
    abstract protected function factory(): callable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new InMemoryContainer();
        $this->setupContainer($this->container);
    }

    private function setupContainer(InMemoryContainer $container): void
    {
        /** @psalm-var mixed $service */
        foreach ($this->dependencies() as $dependency => $service) {
            $container->set($dependency, $service);
        }
    }

    public function testInstantiation(): void
    {
        $factory  = $this->factory();
        $instance = $factory($this->container);
        $this->postCreationAssertions($instance);
    }

    /**
     * Implement this for post creation assertions.
     */
    abstract protected function postCreationAssertions(mixed $instance): void;
}
