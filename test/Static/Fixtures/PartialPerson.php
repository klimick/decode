<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

/**
 * @psalm-immutable
 */
final class PartialPerson
{
    public function __construct(
        public ?string $maybeName,
        public ?int $maybeAge,
    ) {}
}
