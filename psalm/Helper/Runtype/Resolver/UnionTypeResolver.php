<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper\Runtype\Resolver;

use Klimick\PsalmDecode\Helper\Runtype\ResolveArg;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolverInterface;
use Psalm\Type;
use PhpParser\Node;
use Fp\Collections\NonEmptyArrayList;
use Klimick\PsalmTest\Integration\Psalm;
use Fp\Functional\Option\Option;
use function Fp\Evidence\proveOf;

final class UnionTypeResolver implements TypeResolverInterface
{
    public function __invoke(Node\Expr $expr, TypeResolver $resolver): Option
    {
        return proveOf($expr, Node\Expr\FuncCall::class)
            ->filter(fn($expr) => Psalm::isFunctionNameEq($expr, 'Klimick\Decode\Decoder\union'))
            ->flatMap(fn($func) => NonEmptyArrayList::collect($func->args))
            ->flatMap(fn($args) => $args->everyMap(ResolveArg::with($resolver)))
            ->map(fn($types) => Type::combineUnionTypeArray($types->toArray(), codebase: null));
    }
}
