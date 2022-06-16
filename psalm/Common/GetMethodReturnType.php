<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Common;

use Fp\Collections\ArrayList;
use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\InferShape;
use Klimick\Decode\Decoder\InferUnion;
use Klimick\Decode\Test\Static\Fixtures\TaggedUserOrProject;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Psalm\Context;
use Psalm\Internal\Analyzer\FileAnalyzer;
use Psalm\Internal\Analyzer\NamespaceAnalyzer;
use Psalm\Internal\Analyzer\ProjectAnalyzer;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Internal\Provider\NodeDataProvider;
use Psalm\NodeTypeProvider;
use Psalm\StatementsSource;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use function class_exists;
use function Fp\Collection\first;
use function Fp\Evidence\proveClassString;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;
use function in_array;
use function is_subclass_of;
use function method_exists;

final class GetMethodReturnType
{
    /**
     * @param list<string> $deps
     * @return Option<Union>
     *
     * @psalm-suppress InternalMethod
     */
    public static function from(string $class, string $method_name, array $deps = []): Option
    {
        $file_path = proveClassString($class)
            ->map(fn($c) => (new ReflectionClass($c))->getFileName())
            ->flatMap(fn($file) => proveString($file))
            ->get();

        if (null === $file_path) {
            return Option::none();
        }

        $class_storage_mocked = false;
        $file_storage_mocked = false;

        if (!PsalmApi::$codebase->classlike_storage_provider->has($class)) {
            PsalmApi::$codebase->classlike_storage_provider->create($class);
            $class_storage_mocked = true;
        }

        if (!PsalmApi::$codebase->file_storage_provider->has($file_path)) {
            PsalmApi::$codebase->file_storage_provider->create($file_path);
            $file_storage_mocked = true;
        }

        $type = Option::do(function() use ($class, $file_path, $method_name, $deps) {
            $file_statements = yield Option::try(fn() => PsalmApi::$codebase->getStatementsForFile($file_path))
                ->map(fn($stmts) => ArrayList::collect($stmts));

            $single_return_expr = yield self::getSingleReturnExpr($file_statements, $method_name);
            $statements_analyzer = yield self::createStatementsAnalyzer($class, $file_statements, $file_path);

            self::inferComplexTypes(
                self: $class,
                deps: $deps,
                props_expr: $single_return_expr,
                node_data: $statements_analyzer->getNodeTypeProvider(),
            );

            return yield PsalmApi::$types->analyzeType(
                analyzer: $statements_analyzer,
                expr: $single_return_expr,
                context: new Context(),
            );
        });

        if ($class_storage_mocked) {
            PsalmApi::$codebase->classlike_storage_provider->remove($class);
        }

        if ($file_storage_mocked) {
            PsalmApi::$codebase->file_storage_provider->remove($file_path);
        }

        return $type;
    }

    /**
     * @param ArrayList<Node\Stmt> $file_statements
     * @return Option<Expr>
     */
    private static function getSingleReturnExpr(ArrayList $file_statements, string $method_name): Option
    {
        /** @var array<array-key, ClassMethod> $methods */
        $methods = (new NodeFinder())->findInstanceOf($file_statements->toArray(), ClassMethod::class);

        return ArrayList::collect($methods)
            ->first(fn(ClassMethod $method) => $method->name->toString() === $method_name)
            ->flatMap(function(ClassMethod $method) {
                /** @var array<array-key, Return_> $returns */
                $returns = (new NodeFinder())->findInstanceOf($method->stmts ?? [], Return_::class);

                return first($returns)
                    ->flatMap(fn(Return_ $return) => Option::fromNullable($return->expr));
            });
    }

    /**
     * @param ArrayList<Node\Stmt> $stmts
     * @return Option<StatementsSource>
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     */
    public static function createStatementsAnalyzer(string $class, ArrayList $file_statements, string $file_path): Option
    {
        return Option::do(function() use ($class, $file_path, $file_statements) {
            $node_data_provider = new NodeDataProvider();
            $namespace = $file_statements
                ->firstOf(Namespace_::class)
                ->getOrCall(fn() => new Namespace_(
                    stmts: $file_statements
                        ->filterOf(Use_::class)
                        ->toArray(),
                ));

            return yield Option
                ::try(fn() => new FileAnalyzer(
                    project_analyzer: ProjectAnalyzer::$instance,
                    file_path: $file_path,
                    file_name: PsalmApi::$codebase->config->shortenFileName($file_path),
                ))
                ->map(fn(FileAnalyzer $analyzer) => new NamespaceAnalyzer($namespace, $analyzer))
                ->tap(fn(NamespaceAnalyzer $analyzer) => $analyzer->collectAnalyzableInformation())
                ->tap(function(NamespaceAnalyzer $analyzer) use ($class) {
                    if ($class !== TaggedUserOrProject::class) {
                        $analyzer->addSuppressedIssues(['all']);
                        return;
                    }
                    $analyzer->addSuppressedIssues(['UndefinedDocblockClass', 'UndefinedClass']);
                })
                ->map(fn(NamespaceAnalyzer $analyzer) => new StatementsAnalyzer($analyzer, $node_data_provider));
        });
    }

    /**
     * @param list<string> $deps
     */
    public static function inferComplexTypes(string $self, array $deps, Node\Expr $props_expr, NodeTypeProvider $node_data): void
    {
        $visitor = new class($self, $deps, $node_data) extends NodeVisitorAbstract {
            public function __construct(
                private string $self,
                /** @var list<string> */
                private array $deps,
                private NodeTypeProvider $node_data,
            ) {}

            /**
             * @return Option<string>
             */
            private static function getStaticMethodName(Node $node): Option
            {
                return Option::some($node)
                    ->filterOf(Node\Expr\StaticCall::class)
                    ->flatMap(fn($c) => proveOf($c->name, Node\Identifier::class))
                    ->map(fn($id) => $id->name);
            }

            /**
             * @return Option<string>
             */
            private static function getClassNameFromStaticCall(Node $node): Option
            {
                return Option::some($node)
                    ->filterOf(Node\Expr\StaticCall::class)
                    ->flatMap(fn($call) => proveString($call->class->getAttribute('resolvedName')));
            }

            /**
             * @return Option<Union>
             */
            private static function inferFromTypeCall(string $self, Node $node): Option
            {
                return self::getClassNameFromStaticCall($node)
                    ->map(fn($class) => 'self' === $class ? $self : $class)
                    ->filter(fn($class) => class_exists($class) && is_subclass_of($class, InferShape::class))
                    ->filter(fn() => self::getStaticMethodName($node)
                        ->map(fn($method) => 'type' === $method)
                        ->getOrElse(false))
                    ->map(fn($class) => DecoderType::create(DecoderInterface::class, new TNamedObject($class)));
            }

            /**
             * @param list<string> $deps
             * @return Option<Union>
             */
            private static function inferFromShapeCall(array $deps, string $self, Node $node): Option
            {
                return self::getClassNameFromStaticCall($node)
                    ->map(fn($class) => 'self' === $class ? $self : $class)
                    ->filter(fn($class) => !in_array($class, $deps))
                    ->filter(fn($class) => class_exists($class) && is_subclass_of($class, InferShape::class))
                    ->filter(fn() => self::getStaticMethodName($node)
                        ->map(fn($method) => 'shape' === $method)
                        ->getOrElse(false))
                    ->flatMap(fn($class) => GetMethodReturnType::from(
                        class: $class,
                        method_name: 'shape',
                        deps: [...$deps, $class],
                    ));
            }

            /**
             * @param list<string> $deps
             * @return Option<Union>
             */
            private static function inferFromUnionCall(array $deps, string $self, Node $node): Option
            {
                return self::getClassNameFromStaticCall($node)
                    ->map(fn($class) => 'self' === $class ? $self : $class)
                    ->filter(fn($class) => !in_array($class, $deps))
                    ->filter(fn($class) => class_exists($class) && is_subclass_of($class, InferUnion::class))
                    ->filter(fn() => self::getStaticMethodName($node)
                        ->map(fn($method) => 'union' === $method)
                        ->getOrElse(false))
                    ->flatMap(fn($class) => GetMethodReturnType::from(
                        class: $class,
                        method_name: 'union',
                        deps: [...$deps, $class],
                    ));
            }

            /**
             * @return Option<Union>
             */
            private static function inferFromArbitraryStaticCall(string $self, Node $node): Option
            {
                return Option::do(function() use ($self, $node) {
                    $class = yield self::getClassNameFromStaticCall($node)
                        ->filter(fn($class) => 'self' !== $class && $class !== $self)
                        ->filter(fn($class) => class_exists($class))
                        ->filter(fn($class) => !is_subclass_of($class, InferShape::class))
                        ->filter(fn($class) => !is_subclass_of($class, InferUnion::class));

                    $return = yield self::getStaticMethodName($node)
                        ->filter(fn(string $method) => method_exists($class, $method))
                        ->map(fn(string $method) => new ReflectionMethod($class, $method))
                        ->flatMap(fn(ReflectionMethod $method) => proveOf($method->getReturnType(), ReflectionNamedType::class))
                        ->map(fn(ReflectionNamedType $type) => $type->getName())
                        ->map(fn(string $type) => $type === 'self' ? $class : $type);

                    return new Union([
                        new TNamedObject($return),
                    ]);
                });
            }

            /**
             * @return Option<Union>
             */
            private static function inferFromNewExpr(Node $node): Option
            {
                return Option::some($node)
                    ->filterOf(Node\Expr\New_::class)
                    ->flatMap(fn($new) => proveOf($new->class, Node\Name::class))
                    ->flatMap(fn($class) => proveString($class->getAttribute('resolvedName')))
                    ->map(fn($name) => new TNamedObject($name))
                    ->map(fn($name) => new Union([$name]));
            }

            public function leaveNode(Node $node): void
            {
                Option::do(function() use ($node) {
                    $expr = yield proveOf($node, Node\Expr::class);

                    $type = yield self::inferFromTypeCall($this->self, $expr)
                        ->orElse(fn() => self::inferFromShapeCall($this->deps, $this->self, $expr))
                        ->orElse(fn() => self::inferFromUnionCall($this->deps, $this->self, $expr))
                        ->orElse(fn() => self::inferFromArbitraryStaticCall($this->self, $expr))
                        ->orElse(fn() => self::inferFromNewExpr($expr));

                    PsalmApi::$types->setType($this->node_data, $expr, $type);
                });
            }
        };

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse([$props_expr]);
    }
}
