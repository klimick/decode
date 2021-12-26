<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper\Runtype\Resolver;

use Fp\Collections\NonEmptyArrayList;
use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolverInterface;
use Klimick\PsalmTest\Integration\Psalm;
use PhpParser\Node;
use Psalm\Aliases;
use Psalm\Internal\Analyzer\ProjectAnalyzer;
use Psalm\Internal\Analyzer\Statements\Expression\SimpleTypeInferer;
use Psalm\Internal\Provider\NodeDataProvider;
use Psalm\Type;
use function Fp\Evidence\proveOf;

final class LiteralTypeResolver implements TypeResolverInterface
{
    public function __invoke(Node\Expr $expr, TypeResolver $resolver): Option
    {
        return proveOf($expr, Node\Expr\FuncCall::class)
            ->filter(fn($expr) => Psalm::isFunctionNameEq($expr, 'Klimick\Decode\Decoder\literal'))
            ->flatMap(fn($func_call) => NonEmptyArrayList::collect($func_call->args))
            ->flatMap(function($args) {
                return $args->everyMap(fn($arg) => self::infer($arg));
            })
            ->map(fn($types) => Type::combineUnionTypeArray($types->toArray(), codebase: null));
    }

    /**
     * @return Option<Type\Union>
     */
    private static function infer(Node\Arg|Node\VariadicPlaceholder $arg): Option
    {
        return proveOf($arg, Node\Arg::class)
            ->map(fn($arg) => $arg->value)
            ->map(fn($val) => SimpleTypeInferer::infer(
                codebase: ProjectAnalyzer::$instance->getCodebase(),
                nodes: new NodeDataProvider(),
                stmt: $val,
                aliases: new Aliases(),
            ))
            ->flatMap(fn($val) => Option::fromNullable($val));
    }
}
