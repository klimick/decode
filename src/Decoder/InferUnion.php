<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Klimick\Decode\Internal\TaggedUnionDecoder;
use Klimick\Decode\Internal\UnionDecoder;

interface InferUnion
{
    public static function union(): UnionDecoder|TaggedUnionDecoder;
}
