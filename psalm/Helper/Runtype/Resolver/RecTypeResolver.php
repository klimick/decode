<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper\Runtype\Resolver;

use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolverInterface;
use Klimick\PsalmTest\Integration\Psalm;
use PhpParser\Node;
use function Fp\Collection\firstOf;
use function Fp\Evidence\proveOf;

final class RecTypeResolver implements TypeResolverInterface
{
    public function __invoke(Node\Expr $expr, TypeResolver $resolver): Option
    {
        return proveOf($expr, Node\Expr\FuncCall::class)
            ->filter(fn($call) => Psalm::isFunctionNameEq($call, 'Klimick\Decode\Decoder\rec'))
            ->flatMap(fn($call) => firstOf($call->args, Node\Arg::class))
            ->flatMap(fn($arg) => proveOf($arg->value, Node\Expr\ArrowFunction::class))
            ->flatMap(fn($arrow_function) => $resolver($arrow_function->expr));
    }
}
