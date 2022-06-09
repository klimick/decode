<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis;

use Fp\Collections\ArrayList;
use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\InferShape;
use Klimick\PsalmDecode\Common\DecoderType;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Psalm\CodeLocation;
use Psalm\Context;
use Psalm\Internal\Analyzer\FileAnalyzer;
use Psalm\Internal\Analyzer\NamespaceAnalyzer;
use Psalm\Internal\Analyzer\ProjectAnalyzer;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Internal\Provider\NodeDataProvider;
use Psalm\Issue\InvalidReturnStatement;
use Psalm\NodeTypeProvider;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeVisitEvent;
use Psalm\StatementsSource;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;
use ReflectionMethod;
use ReflectionNamedType;
use function class_exists;
use function count;
use function Fp\Collection\first;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;
use function Fp\Evidence\proveTrue;
use function is_subclass_of;
use function method_exists;
use function strtolower;

final class GetMethodReturnType
{
    /**
     * @return Option<Union>
     */
    public static function from(AfterClassLikeVisitEvent $event, string $method_name): Option
    {
        $storage = $event->getStorage();
        $storage->populated = true;

        $type = Option::do(function() use ($event, $storage, $method_name) {
            $single_return_expr = yield self::getSingleReturnExpr($event, $method_name);
            $statements_analyzer = yield self::createStatementsAnalyzer($event);

            return yield PsalmApi::$types->analyzeType(
                analyzer: $statements_analyzer,
                expr: $single_return_expr,
                context: self::createContext(
                    self: $storage->name,
                    props_expr: $single_return_expr,
                    node_data: $statements_analyzer->getNodeTypeProvider(),
                ),
            );
        });

        $storage->populated = false;

        return $type;
    }

    /**
     * @return Option<Expr>
     */
    private static function getSingleReturnExpr(AfterClassLikeVisitEvent $event, string $method_name): Option
    {
        $class = $event->getStmt();

        return ArrayList::collect($class->stmts)
            ->filterOf(ClassMethod::class)
            ->first(fn(ClassMethod $method) => $method->name->toString() === $method_name)
            ->flatMap(function(ClassMethod $method) use ($event) {
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
            });
    }

    /**
     * @return Option<StatementsSource>
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     */
    public static function createStatementsAnalyzer(AfterClassLikeVisitEvent $event): Option
    {
        return Option::do(function() use ($event) {
            $source = $event->getStatementsSource();
            $file_path = $source->getFilePath();

            $file_statements = yield Option::try(fn() => PsalmApi::$codebase->getStatementsForFile($file_path))
                ->map(fn($stmts) => ArrayList::collect($stmts));

            $storage = $event->getStorage();
            $node_data_provider = new NodeDataProvider();
            $namespace = $file_statements
                ->firstOf(Namespace_::class)
                ->getOrCall(fn() => new Namespace_(
                    stmts: $file_statements
                        ->filterOf(Use_::class)
                        ->toArray(),
                ));

            return yield Option
                ::try(fn() => ProjectAnalyzer::$instance->getFileAnalyzerForClassLike($storage->name))
                ->map(fn(FileAnalyzer $analyzer) => new NamespaceAnalyzer($namespace, $analyzer))
                ->tap(fn(NamespaceAnalyzer $analyzer) => $analyzer->collectAnalyzableInformation())
                ->tap(fn(NamespaceAnalyzer $analyzer) => $analyzer->addSuppressedIssues(['all']))
                ->map(fn(NamespaceAnalyzer $analyzer) => new StatementsAnalyzer($analyzer, $node_data_provider));
        });
    }

    public static function createContext(string $self, Node\Expr $props_expr, NodeTypeProvider $node_data): Context
    {
        $visitor = new class($self, $node_data) extends NodeVisitorAbstract {
            /** @var list<string> */
            private array $phantom_classes = [];

            public function __construct(
                private string $self,
                private NodeTypeProvider $node_data,
            ) {}

            /**
             * @return Option<array{string, Union}>
             */
            private static function inferFromTypeCall(string $self, Node $node): Option
            {
                return Option::do(function() use ($self, $node) {
                    $method = yield Option::some($node)
                        ->filterOf(Node\Expr\StaticCall::class)
                        ->flatMap(fn($c) => proveOf($c->name, Node\Identifier::class))
                        ->map(fn($id) => $id->name);

                    $class = yield Option::some($node)
                        ->filterOf(Node\Expr\StaticCall::class)
                        ->flatMap(fn($call) => proveString($call->class->getAttribute('resolvedName')))
                        ->map(fn($class) => 'self' === $class ? $self : $class)
                        ->filter(fn($class) => class_exists($class) && is_subclass_of($class, InferShape::class))
                        ->filter(fn() => 'type' === $method);

                    $return = DecoderType::create(DecoderInterface::class, new TNamedObject($class));

                    return [$class, $return];
                });
            }

            /**
             * @return Option<array{string, Union}>
             */
            private static function inferFromArbitraryStaticCall(string $self, Node $node): Option
            {
                return Option::do(function() use ($self, $node) {
                    $method = yield Option::some($node)
                        ->filterOf(Node\Expr\StaticCall::class)
                        ->flatMap(fn($c) => proveOf($c->name, Node\Identifier::class))
                        ->map(fn($id) => $id->name);

                    $class = yield Option::some($node)
                        ->filterOf(Node\Expr\StaticCall::class)
                        ->flatMap(fn($call) => proveString($call->class->getAttribute('resolvedName')))
                        ->filter(fn($class) => 'self' !== $class && $class !== $self)
                        ->filter(fn($class) => class_exists($class));

                    $return = yield proveTrue(method_exists($class, $method))
                        ->map(fn() => new ReflectionMethod($class, $method))
                        ->map(fn(ReflectionMethod $reflection) => $reflection->getReturnType())
                        ->filterOf(ReflectionNamedType::class)
                        ->map(fn(ReflectionNamedType $type) => $type->getName())
                        ->map(fn($type) => $type === 'self' ? $class : $type);

                    return [
                        $class,
                        new Union([
                            new TNamedObject($return),
                        ]),
                    ];
                });
            }

            /**
             * @return Option<array{string, Union}>
             */
            private static function inferFromNewExpr(Node $node): Option
            {
                return Option::some($node)
                    ->filterOf(Node\Expr\New_::class)
                    ->flatMap(fn($new) => proveOf($new->class, Node\Name::class))
                    ->flatMap(fn($class) => proveString($class->getAttribute('resolvedName')))
                    ->map(fn($name) => [
                        $name,
                        new Union([
                            new TNamedObject($name),
                        ]),
                    ]);
            }

            public function leaveNode(Node $node): void
            {
                Option::do(function() use ($node) {
                    $expr = yield proveOf($node, Node\Expr::class);

                    [$phantom, $type] = yield self::inferFromTypeCall($this->self, $expr)
                        ->orElse(fn() => self::inferFromArbitraryStaticCall($this->self, $expr))
                        ->orElse(fn() => self::inferFromNewExpr($expr));

                    PsalmApi::$types->setType($this->node_data, $expr, $type);
                    $this->phantom_classes[] = $phantom;
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

        $context = new Context();
        $context->inside_general_use = true;
        $context->pure = true;
        $context->self = $self;

        foreach ($visitor->getPhantomClasses() as $phantom_class) {
            $context->phantom_classes[strtolower($phantom_class)] = true;
        }

        return $context;
    }
}
