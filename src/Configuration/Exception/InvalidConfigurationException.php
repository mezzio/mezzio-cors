<?php

declare(strict_types=1);

namespace Mezzio\Cors\Configuration\Exception;

use Mezzio\Cors\Exception\AbstractInvalidArgumentException;

final class InvalidConfigurationException extends AbstractInvalidArgumentException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function create(string $message): self
    {
        return new self($message);
    }
}
