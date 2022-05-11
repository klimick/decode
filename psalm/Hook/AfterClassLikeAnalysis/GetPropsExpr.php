<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis;

use Fp\Collections\ArrayList;
use Fp\Functional\Option\Option;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use function Fp\Collection\firstOf;
use function Fp\Evidence\proveOf;

final class GetPropsExpr
{
    /**
     * @return Option<FuncCall>
     */
    public static function from(ClassLike $class): Option
    {
        return ArrayList::collect($class->stmts)
            ->filterOf(ClassMethod::class)
            ->first(fn(ClassMethod $method) => $method->name->toString() === 'props')
            ->flatMap(fn(ClassMethod $method) => firstOf($method->stmts ?? [], Return_::class))
            ->flatMap(fn(Return_ $return) => proveOf($return->expr, FuncCall::class))
            ->filter(fn(FuncCall $func) => proveOf($func->name, Name::class)
                ->map(fn(Name $name) => 'Klimick\Decode\Decoder\shape' === $name->getAttribute('resolvedName'))
                ->getOrElse(false));
    }
}
