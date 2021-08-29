<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

/**
 * @psalm-immutable
 */
final class UnionCaseErrors
{
    /**
     * @param non-empty-string $case
     * @param non-empty-list<DecodeErrorInterface> $errors
     */
    public function __construct(
        public string $case,
        public array $errors,
    ) {}
}
