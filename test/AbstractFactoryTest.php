<?php

declare(strict_types=1);

namespace Mezzio\CorsTest;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function gettype;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;

abstract class AbstractFactoryTest extends TestCase
{
    /**
     * @var callable
     */
    protected $factory;

    /**
     * @psalm-var MockObject&ContainerInterface
     */
    private $container;

    /**
     * @return array<string,string|array|object>
     */
    abstract protected function dependencies(): array;

    abstract protected function factory(): callable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory   = $this->factory();
        $this->container = $this->createMock(ContainerInterface::class);
        $this->setupContainer($this->container);
    }

    /**
     * @psalm-param ContainerInterface&MockObject $container
     */
    private function setupContainer(MockObject $container): void
    {
        $dependencies = $this->dependencies();
        if (! $dependencies) {
            return;
        }

        $consecutiveParameters = $consecutiveReturnValues = [];
        foreach ($dependencies as $dependency => $definition) {
            $consecutiveParameters[]   = [$dependency];
            $consecutiveReturnValues[] = $this->createReturnValueFromDefinition($definition);
        }

        $container
            ->expects($this->any())
            ->method('get')
            ->withConsecutive(...$consecutiveParameters)
            ->willReturnOnConsecutiveCalls(...$consecutiveReturnValues);
    }

    public function testInstantiation(): void
    {
        $factory  = $this->factory;
        $instance = $factory($this->container);
        $this->postCreationAssertions($instance);
    }

    /**
     * @param string|array|object $definition
     * @return array|object
     */
    private function createReturnValueFromDefinition($definition)
    {
        if (is_string($definition)) {
            return $this->createMock($definition);
        }

        if (is_object($definition)) {
            return $definition;
        }

        if (is_array($definition)) {
            return $definition;
        }

        $this->fail(sprintf(
            'Invalid return value definition provided for factory test: %s',
            gettype($definition)
        ));
    }

    /**
     * Implement this for post creation assertions.
     *
     * @param mixed $instance
     */
    abstract protected function postCreationAssertions($instance): void;
}
