<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper\Runtype\Resolver;

use Klimick\PsalmDecode\Helper\Runtype\ResolveArg;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\TypeResolverInterface;
use Psalm\Type;
use PhpParser\Node;
use Klimick\PsalmTest\Integration\Psalm;
use Fp\Functional\Option\Option;
use function Fp\Collection\first;
use function Fp\Collection\second;
use function Fp\Evidence\proveOf;

final class ArrListTypeResolver implements TypeResolverInterface
{
    public function __invoke(Node\Expr $expr, TypeResolver $resolver): Option
    {
        return Option::do(function() use ($expr, $resolver) {
            $func_call = yield proveOf($expr, Node\Expr\FuncCall::class);
            $func_name = yield Psalm::getFunctionName($func_call);

            return yield self::toList($func_call, $resolver, $func_name)
                ->orElse(fn() => self::toArray($func_call, $resolver, $func_name));
        });
    }

    /**
     * @return Option<Type\Union>
     */
    private static function toArray(Node\Expr\FuncCall $func, TypeResolver $resolver, string $function): Option
    {
        return Option::do(function() use ($func, $resolver, $function) {
            $key_type = yield first($func->args)->flatMap(ResolveArg::with($resolver));
            $val_type = yield second($func->args)->flatMap(ResolveArg::with($resolver));

            $array_atomic = yield Option::fromNullable(
                match ($function) {
                    'Klimick\Decode\Decoder\arr' => new Type\Atomic\TArray([$key_type, $val_type]),
                    'Klimick\Decode\Decoder\nonEmptyArr' => new Type\Atomic\TNonEmptyArray([$key_type, $val_type]),
                    default => null,
                }
            );

            return new Type\Union([$array_atomic]);
        });
    }

    /**
     * @return Option<Type\Union>
     */
    private static function toList(Node\Expr\FuncCall $func, TypeResolver $resolver, string $function): Option
    {
        return first($func->args)
            ->flatMap(ResolveArg::with($resolver))
            ->flatMap(fn($val_type) => Option::fromNullable(
                match ($function) {
                    'Klimick\Decode\Decoder\arrList' => new Type\Atomic\TList($val_type),
                    'Klimick\Decode\Decoder\nonEmptyArrList' => new Type\Atomic\TNonEmptyList($val_type),
                    default => null,
                }
            ))
            ->map(fn($atomic) => new Type\Union([$atomic]));
    }
}
