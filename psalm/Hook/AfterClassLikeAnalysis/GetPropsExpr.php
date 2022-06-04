<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis;

use Fp\Collections\ArrayList;
use Fp\Functional\Option\Option;
use PhpParser\Node;
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

final class GetPropsExpr
{
    /**
     * @return Option<Expr>
     */
    public static function from(AfterClassLikeVisitEvent $event): Option
    {
        $class = $event->getStmt();

        return ArrayList::collect($class->stmts)
            ->filterOf(ClassMethod::class)
            ->first(fn(ClassMethod $method) => $method->name->toString() === 'props')
            ->flatMap(fn(ClassMethod $method) => self::getExprFromSingleReturn($event, $method));
    }

    /**
     * @param list<Stmt> $method_stmts
     * @return Option<Expr>
     */
    private static function getExprFromSingleReturn(AfterClassLikeVisitEvent $event, ClassMethod $props_method): Option
    {
        /** @var array<array-key, Return_> $returns */
        $returns = (new NodeFinder())->find($props_method->stmts ?? [], fn(Node $node) => $node instanceof Return_);

        if (count($returns) > 1) {
            $storage = $event->getStorage();
            $storage->docblock_issues[] = new InvalidReturnStatement(
                message: 'Props method must have only one return statement',
                code_location: new CodeLocation($event->getStatementsSource(), $props_method),
            );

            return Option::none();
        }

        return first($returns)->flatMap(fn(Return_ $return) => Option::fromNullable($return->expr));
    }
}
