<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder as t;
use Klimick\Decode\Internal\UnionDecoder;

/**
 * @mixin UserOrProjectMetaMixin
 */
final class UserOrProject implements t\InferUnion
{
    use t\UnionInstance;

    public static function union(): UnionDecoder
    {
        return t\union(User::type(), Project::type());
    }
}
