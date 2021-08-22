<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

/**
 * @psalm-immutable
 */
final class Person
{
    public function __construct(
        public string $name,
        public int $age,
    ) {}
}
