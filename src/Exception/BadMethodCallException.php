<?php

declare(strict_types=1);

namespace Mezzio\Cors\Exception;

use BadMethodCallException as BaseBadMethodCallException;

use function sprintf;

final class BadMethodCallException extends BaseBadMethodCallException implements ExceptionInterface
{
    public static function fromMissingSetterMethod(string $property, string $expectedSetterMethod): self
    {
        return new self(sprintf(
            'Missing setter method for property %s; expected setter %s',
            $property,
            $expectedSetterMethod
        ));
    }
}
