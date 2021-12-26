<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper\Runtype;

use Closure;
use Fp\Functional\Option\Option;
use PhpParser\Node;
use Psalm\Type\Union;

final class ResolveArg
{
    /**
     * @return Closure(Node\Arg|Node\VariadicPlaceholder): Option<Union>
     */
    public static function with(TypeResolver $resolver): Closure
    {
        return fn(Node\Arg|Node\VariadicPlaceholder $arg) => Option::some($arg)
            ->filterOf(Node\Arg::class)
            ->map(fn($arg) => $arg->value)
            ->flatMap($resolver);
    }
}
