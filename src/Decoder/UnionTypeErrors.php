<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

/**
 * @psalm-immutable
 */
final class UnionTypeErrors implements DecodeErrorInterface
{
    /**
     * @param non-empty-list<UnionCaseErrors> $errors
     */
    public function __construct(public array $errors)
    {
    }
}
