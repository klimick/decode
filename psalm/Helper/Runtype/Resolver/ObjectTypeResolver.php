<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper\Runtype\Resolver;

use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolverInterface;
use Klimick\PsalmTest\Integration\Psalm;
use PhpParser\Node;
use Psalm\Type;
use function Fp\Collection\firstOf;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;

final class ObjectTypeResolver implements TypeResolverInterface
{
    public function __invoke(Node\Expr $expr, TypeResolver $resolver): Option
    {
        return proveOf($expr, Node\Expr\FuncCall::class)
            ->flatMap(fn($call) => proveOf($call->name, Node\Expr\FuncCall::class))
            ->filter(fn($call) => Psalm::isFunctionNameEq($call,
                'Klimick\Decode\Decoder\object',
                'Klimick\Decode\Decoder\partialObject'))
            ->flatMap(fn($call) => firstOf($call->args, Node\Arg::class))
            ->flatMap(fn($arg) => proveOf($arg->value, Node\Expr\ClassConstFetch::class))
            ->flatMap(fn($fetch) => proveString($fetch->class->getAttribute('resolvedName')))
            ->map(fn($class) => new Type\Atomic\TNamedObject($class))
            ->map(fn($named_object) => new Type\Union([$named_object]));
    }
}
