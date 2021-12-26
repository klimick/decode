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

final class OptionalTypeResolver implements TypeResolverInterface
{
    public function __invoke(Node\Expr $expr, TypeResolver $resolver): Option
    {
        return proveOf($expr, Node\Expr\MethodCall::class)
            ->filter(fn($method_call) => Psalm::isMethodNameEq($method_call, 'optional'))
            ->flatMap(fn($method_call) => $resolver($method_call->var))
            ->tap(fn(Type\Union $type) => $type->possibly_undefined = true);
    }
}
