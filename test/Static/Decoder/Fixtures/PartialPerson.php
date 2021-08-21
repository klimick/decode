<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder\Fixtures;

/**
 * @psalm-immutable
 */
final class PartialPerson
{
    public function __construct(
        public ?string $name,
        public ?int $age,
    ) {}
}
