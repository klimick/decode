<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Internal\UnionDecoder;
use Psalm\Type\Atomic\TObject;

/**
 * @template TObject of object
 * @psalm-immutable
 */
final class SumCases
{
    /**
     * @param non-empty-array<non-empty-string, ObjectDecoder<TObject> | UnionDecoder<TObject>> $cases
     */
    public function __construct(public array $cases)
    {
    }
}
