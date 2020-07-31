<?php

declare(strict_types=1);

namespace Mezzio\Cors\Exception;

use RuntimeException;
use Throwable;

use function sprintf;

final class InvalidOriginValueException extends RuntimeException implements ExceptionInterface
{
    private function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function fromThrowable(string $origin, Throwable $throwable): self
    {
        return new self(sprintf('Provided Origin "%s" is invalid.', $origin), $throwable);
    }
}
