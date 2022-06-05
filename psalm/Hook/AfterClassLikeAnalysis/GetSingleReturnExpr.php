<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis;

use Fp\Collections\ArrayList;
use Fp\Functional\Option\Option;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use Psalm\CodeLocation;
use Psalm\Issue\InvalidReturnStatement;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeVisitEvent;
use function count;
use function Fp\Collection\first;

final class GetSingleReturnExpr
{
    /**
     * @return Option<Expr>
     */
    public static function for(AfterClassLikeVisitEvent $event, string $method_name): Option
    {
        $class = $event->getStmt();

        return ArrayList::collect($class->stmts)
            ->filterOf(ClassMethod::class)
            ->first(fn(ClassMethod $method) => $method->name->toString() === $method_name)
            ->flatMap(fn(ClassMethod $method) => self::getExprFromSingleReturn($event, $method));
    }

    /**
     * @param list<Stmt> $method_stmts
     * @return Option<Expr>
     */
    private static function getExprFromSingleReturn(AfterClassLikeVisitEvent $event, ClassMethod $method): Option
    {
        /** @var array<array-key, Return_> $returns */
        $returns = (new NodeFinder())->findInstanceOf($method->stmts ?? [], Return_::class);

        if (count($returns) > 1) {
            $storage = $event->getStorage();

            $storage->docblock_issues[] = new InvalidReturnStatement(
                message: "Method '{$method->name->name}' must have only one return statement",
                code_location: new CodeLocation($event->getStatementsSource(), $method),
            );

            return Option::none();
        }

        return first($returns)->flatMap(fn(Return_ $return) => Option::fromNullable($return->expr));
    }
}