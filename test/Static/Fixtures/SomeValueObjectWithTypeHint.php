<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use InvalidArgumentException;
use function is_string;

/**
 * @psalm-immutable
 */
final class SomeValueObjectWithTypeHint
{
    public function __construct(public string $value) {}

    public static function create(mixed $value): SomeValueObjectWithTypeHint
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Expected string');
        }

        return new SomeValueObjectWithTypeHint($value);
    }
}
