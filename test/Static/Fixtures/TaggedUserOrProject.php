<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder as t;

/**
 * @mixin TaggedUserOrProjectMetaMixin
 */
final class TaggedUserOrProject implements t\InferUnion
{
    use t\UnionInstance;

    public static function union(): t\TaggedUnionDecoder
    {
        return t\tagged(with: 'type')(
            user: User::type(),
            project: Project::type(),
        );
    }
}
