<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

/**
 * @psalm-immutable
 */
final class UnionTypeErrors implements DecodeErrorInterface
{
    /**
     * @param non-empty-array<non-empty-string, non-empty-list<DecodeErrorInterface>> $errors
     */
    public function __construct(public array $errors)
    {
    }
}
