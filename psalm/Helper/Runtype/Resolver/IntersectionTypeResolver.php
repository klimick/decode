<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper\Runtype\Resolver;

use Psalm\Type;
use PhpParser\Node;
use Klimick\PsalmTest\Integration\Psalm;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolverInterface;
use Fp\Functional\Option\Option;
use function Fp\Evidence\proveNonEmptyList;
use function Fp\Evidence\proveOf;

final class IntersectionTypeResolver implements TypeResolverInterface
{
    public function __invoke(Node\Expr $expr, TypeResolver $resolver): Option
    {
        return Option::do(function() use ($expr, $resolver) {
            $args = yield proveOf($expr, Node\Expr\FuncCall::class)
                ->filter(fn($call) => Psalm::isFunctionNameEq($call, 'Klimick\Decode\Decoder\intersection'))
                ->flatMap(fn($call) => proveNonEmptyList($call->args));

            $all_properties = [];

            foreach ($args as $arg) {
                $all_properties = yield proveOf($arg, Node\Arg::class)
                    ->flatMap(fn($arg) => $resolver($arg->value))
                    ->flatMap(fn($union) => Psalm::asSingleAtomicOf(Type\Atomic\TKeyedArray::class, $union))
                    ->map(fn($keyed_array) => array_merge($all_properties, $keyed_array->properties));
            }

            return new Type\Union([
                new Type\Atomic\TKeyedArray($all_properties),
            ]);
        });
    }
}
