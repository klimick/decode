<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

interface InferUnion
{
    public static function union(): UnionDecoder|TaggedUnionDecoder;
}
