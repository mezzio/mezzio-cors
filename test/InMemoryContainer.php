<?php

declare(strict_types=1);

namespace Mezzio\CorsTest;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

use function array_key_exists;

final class InMemoryContainer implements ContainerInterface
{
    /** @var array<string,mixed> */
    private array $services = [];

    public function get(string $id): mixed
    {
        if (! $this->has($id)) {
            throw new class ($id . ' was not found') extends RuntimeException implements NotFoundExceptionInterface {
            };
        }

        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }

    public function set(string $id, mixed $item): void
    {
        $this->services[$id] = $item;
    }
}
