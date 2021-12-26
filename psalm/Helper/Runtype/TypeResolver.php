<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper\Runtype;

use Fp\Functional\Option\Option;
use Fp\Streams\Stream;
use PhpParser\Node;
use Psalm\Type;

final class TypeResolver
{
    /**
     * @param list<TypeResolverInterface> $resolvers
     */
    public function __construct(private array $resolvers)
    {
    }

    /**
     * @return Option<Type\Union>
     */
    public function __invoke(Node\Expr $expr): Option
    {
        return Stream::emits($this->resolvers)
            ->filterMap(fn($resolver) => $resolver($expr, $this))
            ->head();
    }
}
