<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper\Runtype;

use Fp\Collections\ArrayList;
use Fp\Collections\NonEmptyArrayList;
use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\Helper\Runtype\Resolver\ArrListTypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\Resolver\AtomicTypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\Resolver\FromJsonTypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\Resolver\IntersectionTypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\Resolver\LiteralTypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\Resolver\ObjectTypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\Resolver\OptionalTypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\Resolver\RecTypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\Resolver\RuntypeTypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\Resolver\ShapeTypeResolver;
use Klimick\PsalmDecode\Helper\Runtype\Resolver\UnionTypeResolver;
use Klimick\PsalmTest\Integration\Psalm;
use PhpParser\Node;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeVisitEvent;
use Psalm\Type;
use function Fp\Collection\at;
use function Fp\Collection\filterOf;
use function Fp\Collection\first;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

final class TypedArgGrabber
{
    /**
     * @return Option<NonEmptyArrayList<TypedArg>>
     */
    public static function grab(AfterClassLikeVisitEvent $event, string $parent_class, string $forMetaFunction): Option
    {
        return self::isClassExtend($parent_class, $event)
            ->flatMap(fn() => self::getFunctionCallArgs($event, $forMetaFunction))
            ->flatMap(fn($args) => $args->everyMap(fn($arg) => self::asTyped($arg)));
    }

    /**
     * @return Option<void>
     */
    private static function isClassExtend(string $extends, AfterClassLikeVisitEvent $event): Option
    {
        return Option::do(function() use ($event, $extends) {
            $class = yield proveOf($event->getStmt(), Node\Stmt\Class_::class);
            $aliases = $event->getStatementsSource()->getAliases();

            $extends_fqn = yield Option::fromNullable($class->extends)
                ->map(fn($id) => $id->toString())
                ->map(fn($id) => strtolower($id))
                ->flatMap(fn($id) => at($aliases->uses, $id));

            yield proveTrue($extends === $extends_fqn);
        });
    }

    /**
     * @return Option<NonEmptyArrayList<Node\Arg>>
     */
    public static function getFunctionCallArgs(AfterClassLikeVisitEvent $event, string $function): Option
    {
        return proveOf($event->getStmt(), Node\Stmt\Class_::class)
            ->flatMap(fn($class) => self::getSingleReturnStatement($class))
            ->flatMap(fn($expr) => proveOf($expr, Node\Expr\FuncCall::class))
            ->filter(fn($call) => Psalm::isFunctionNameEq($call, $function))
            ->flatMap(fn($func_call) => NonEmptyArrayList::collect($func_call->args))
            ->flatMap(fn($args) => $args->everyMap(
                fn($a) => $a instanceof Node\Arg ? Option::some($a) : Option::none()
            ));
    }

    /**
     * @return Option<Node\Expr>
     */
    private static function getSingleReturnStatement(Node\Stmt\Class_ $class): Option
    {
        return ArrayList::collect($class->stmts)
            ->firstOf(Node\Stmt\ClassMethod::class)
            ->filter(fn($class_method) => $class_method->name->toString() === 'definition')
            ->flatMap(fn($class_method) => Option::fromNullable($class_method->stmts))
            ->map(fn($stmts) => filterOf($stmts, Node\Stmt\Return_::class))
            ->filter(fn($stmts) => 1 === count($stmts))
            ->flatMap(fn($stmts) => first($stmts))
            ->flatMap(fn($stmt) => Option::fromNullable($stmt->expr));
    }

    /**
     * @return Option<TypedArg>
     */
    private static function asTyped(Node\Arg $arg): Option
    {
        return proveOf($arg->name, Node\Identifier::class)
            ->map(function($arg_id) use ($arg) {
                $resolver = new TypeResolver([
                    new AtomicTypeResolver(),
                    new UnionTypeResolver(),
                    new ArrListTypeResolver(),
                    new OptionalTypeResolver(),
                    new LiteralTypeResolver(),
                    new FromJsonTypeResolver(),
                    new ShapeTypeResolver(),
                    new RuntypeTypeResolver(),
                    new ObjectTypeResolver(),
                    new IntersectionTypeResolver(),
                    new RecTypeResolver(),
                ]);

                $type = $resolver($arg->value)
                    ->getOrCall(fn() => Type::getMixed());

                return new TypedArg($arg_id->name, $type);
            });
    }
}
