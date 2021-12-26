<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper\Runtype\Resolver;

use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolverInterface;
use Klimick\PsalmTest\Integration\Psalm;
use PhpParser\Node;
use Psalm\Type;
use function Fp\Evidence\proveOf;

final class RuntypeTypeResolver implements TypeResolverInterface
{
    public function __invoke(Node\Expr $expr, TypeResolver $resolver): Option
    {
        return proveOf($expr, Node\Expr\StaticCall::class)
            ->filter(fn($static_call) => Psalm::isMethodNameEq($static_call, 'type'))
            ->flatMap(fn($static_call) => Psalm::getClassFromStaticCall($static_call))
            ->map(fn($called_class) => new Type\Atomic\TNamedObject($called_class))
            ->map(fn($named_object) => new Type\Union([$named_object]));
    }
}
