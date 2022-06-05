<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\DecoderInterface;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Psalm\Context;
use Psalm\NodeTypeProvider;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;

final class CreateContext
{
    public static function for(string $self, Node\Expr $props_expr, NodeTypeProvider $node_data): Context
    {
        $visitor = new class($node_data) extends NodeVisitorAbstract {
            /** @var list<string> */
            private array $phantom_classes = [];

            public function __construct(
                private NodeTypeProvider $node_data,
            ) {}

            /**
             * @return Option<TGenericObject>
             */
            private static function fromStaticCall(Node $node): Option
            {
                return Option::do(function() use ($node) {
                    $method_name = yield Option::some($node)
                        ->filterOf(Node\Expr\StaticCall::class)
                        ->flatMap(fn($c) => proveOf($c->name, Node\Identifier::class))
                        ->map(fn($id) => $id->name);

                    $class_name = yield Option::some($node)
                        ->filterOf(Node\Expr\StaticCall::class)
                        ->flatMap(fn($c) => proveString($c->class->getAttribute('resolvedName')))
                        ->filter(fn($c) => class_exists($c) && is_subclass_of($c, \Klimick\Decode\Decoder\InferShape::class))
                        ->filter(fn() => 'type' === $method_name);

                    return new TGenericObject(DecoderInterface::class, [
                        new Union([
                            new TNamedObject($class_name),
                        ]),
                    ]);
                });
            }

            /**
             * @return Option<TNamedObject>
             */
            private static function fromNew(Node $node): Option
            {
                return Option::some($node)
                    ->filterOf(Node\Expr\New_::class)
                    ->flatMap(fn($n) => proveOf($n->class, Node\Name::class))
                    ->flatMap(fn($n) => proveString($n->getAttribute('resolvedName')))
                    ->map(fn($n) => new TNamedObject($n));
            }

            public function leaveNode(Node $node): void
            {
                Option::do(function() use ($node) {
                    $expr = yield proveOf($node, Node\Expr::class);

                    $named_object = yield self::fromStaticCall($expr)
                        ->orElse(fn() => self::fromNew($expr));

                    $this->node_data->setType($expr, new Union([$named_object]));
                    $this->phantom_classes[] = $named_object->value;
                });
            }

            /**
             * @return list<string>
             */
            public function getPhantomClasses(): array
            {
                return $this->phantom_classes;
            }
        };

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse([$props_expr]);

        return self::createContext($self, $visitor->getPhantomClasses());
    }

    /**
     * @param list<string> $phantom_classes
     */
    private static function createContext(string $self, array $phantom_classes): Context
    {
        $context = new Context();
        $context->inside_general_use = true;
        $context->pure = true;
        $context->self = $self;

        foreach ($phantom_classes as $phantom_class) {
            $context->phantom_classes[strtolower($phantom_class)] = true;
        }

        return $context;
    }
}
