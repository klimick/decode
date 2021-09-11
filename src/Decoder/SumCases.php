<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Internal\UnionDecoder;

/**
 * @psalm-immutable
 */
final class SumCases
{
    /**
     * @param non-empty-array<non-empty-string, ObjectDecoder<ProductType> | UnionDecoder<SumType>> $cases
     */
    public function __construct(public array $cases)
    {
    }
}
