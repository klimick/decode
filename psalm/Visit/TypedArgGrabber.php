<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Visit;

use Fp\Collections\ArrayList;
use Fp\Collections\NonEmptyArrayList;
use Fp\Functional\Unit;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\PsalmDecode\NamedArguments\ClassTypeUpcast;
use PhpParser\Node;
use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\NamedArguments\DecoderTypeParamExtractor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Psalm\Context;
use Psalm\FileSource;
use Psalm\Internal\Analyzer\NamespaceAnalyzer;
use Psalm\Internal\Analyzer\ProjectAnalyzer;
use Psalm\Internal\Analyzer\Statements\Expression\Call\FunctionCallAnalyzer;
use Psalm\Internal\Analyzer\Statements\Expression\Call\MethodCallAnalyzer;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Internal\Provider\NodeDataProvider;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeVisitEvent;
use function Fp\Collection\at;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;
use function Fp\unit;

final class TypedArgGrabber
{
    /**
     * @return Option<NonEmptyArrayList<TypedArg>>
     */
    public static function grab(AfterClassLikeVisitEvent $fromEvent, string $forSubClassOf, string $forMetaFunction): Option
    {
        return Option::do(function() use ($fromEvent, $forSubClassOf, $forMetaFunction) {
            $class = yield self::getVisitClassName($fromEvent, $forSubClassOf);
            $analyzer = yield self::createStatementsAnalyzer($fromEvent, $class);
            $call_args = yield self::getFunctionCallArgs($fromEvent, $forMetaFunction);

            return yield $call_args->everyMap(fn($arg) => self::asTyped($arg, $analyzer));
        });
    }

    /**
     * @return Option<string>
     */
    private static function getVisitClassName(AfterClassLikeVisitEvent $event, string $extends): Option
    {
        return Option::do(function() use ($event, $extends) {
            $class = yield proveOf($event->getStmt(), Node\Stmt\Class_::class);
            $aliases = $event->getStatementsSource()->getAliases();

            $extends_fqn = yield Option::fromNullable($class->extends)
                ->map(fn($id) => $id->toString())
                ->map(fn($id) => strtolower($id))
                ->flatMap(fn($id) => at($aliases->uses, $id));

            yield proveTrue($extends === $extends_fqn);

            return yield Option::fromNullable($class->name)
                ->map(fn($id) => $id->name)
                ->map(fn($id) => null === $aliases->namespace ? $id : "{$aliases->namespace}\\{$id}");
        });
    }

    /**
     * @return Option<StatementsAnalyzer>
     */
    private static function createStatementsAnalyzer(AfterClassLikeVisitEvent $event, string $class_fqn): Option
    {
        return Option::do(function() use ($class_fqn, $event) {
            $file_statements = yield self::getFileStatements($event->getStatementsSource());

            $namespace = $file_statements
                ->firstOf(Node\Stmt\Namespace_::class)
                ->getOrCall(fn() => new Node\Stmt\Namespace_(
                    stmts: $file_statements
                        ->filterOf(Node\Stmt\Use_::class)
                        ->toArray()
                ));

            return yield Option
                ::try(fn() => ProjectAnalyzer::$instance->getFileAnalyzerForClassLike($class_fqn))
                ->map(fn($analyzer) => new NamespaceAnalyzer($namespace, $analyzer))
                ->tap(fn($analyzer) => $analyzer->collectAnalyzableInformation())
                ->map(fn($analyzer) => new StatementsAnalyzer(
                    source: $analyzer,
                    node_data: new NodeDataProvider(),
                ))
                ->tap(function(StatementsAnalyzer $analyzer) {
                    $analyzer->addSuppressedIssues(['all']);
                });
        });
    }

    /**
     * @return Option<ArrayList<Node\Stmt>>
     */
    private static function getFileStatements(FileSource $source): Option
    {
        $codebase = ProjectAnalyzer::$instance->getCodebase();
        $file_path = $source->getFilePath();

        return Option::try(fn() => $codebase->getStatementsForFile($file_path))
            ->map(fn($stmts) => ArrayList::collect($stmts));
    }

    /**
     * @return Option<NonEmptyArrayList<Node\Arg>>
     */
    public static function getFunctionCallArgs(AfterClassLikeVisitEvent $event, string $function): Option
    {
        return Option::do(function() use ($event, $function) {
            $class = yield proveOf($event->getStmt(), Node\Stmt\Class_::class);
            $return = yield self::getSingleReturnStatement($class);
            $func_call = yield proveOf($return->expr, Node\Expr\FuncCall::class);
            $func_name = yield proveOf($func_call->name, Node\Name::class);
            yield proveTrue($function === $func_name->getAttribute('resolvedName'));

            $args = yield NonEmptyArrayList::collect($func_call->args);

            return yield $args->everyMap(
                fn($a) => $a instanceof Node\Arg
                    ? Option::some($a)
                    : Option::none()
            );
        });
    }

    /**
     * @return Option<Node\Stmt\Return_>
     */
    private static function getSingleReturnStatement(Node\Stmt\Class_ $class): Option
    {
        return ArrayList::collect($class->stmts)
            ->firstOf(Node\Stmt\ClassMethod::class)
            ->filter(fn($class_method) => $class_method->name->toString() === 'definition')
            ->flatMap(fn($class_method) => Option::fromNullable($class_method->stmts))
            ->map(fn($stmts) => ArrayList::collect($stmts)->filterOf(Node\Stmt\Return_::class))
            ->filter(fn($stmts) => 1 === $stmts->count())
            ->flatMap(fn($stmts) => $stmts->firstElement());
    }

    /**
     * @return Option<TypedArg>
     */
    private static function asTyped(Node\Arg $arg, StatementsAnalyzer $analyzer): Option
    {
        return Option::do(function() use ($arg, $analyzer) {
            yield self::analyzeDecoderTypes($arg->value, $analyzer);

            return new TypedArg(
                name: yield proveOf($arg->name, Node\Identifier::class)->map(fn($id) => $id->name),
                type: yield Option::fromNullable($analyzer->node_data->getType($arg->value))
                    ->flatMap(fn($union) => DecoderTypeParamExtractor::extract($union)),
            );
        });
    }

    /**
     * @return Option<Unit>
     */
    public static function analyzeDecoderTypes(Node\Expr $expr, StatementsAnalyzer $analyzer): Option
    {
        $analyze_method_call = Option::do(function() use ($expr, $analyzer) {
            $method_call = yield proveOf($expr, Node\Expr\MethodCall::class);

            if ($method_call->var instanceof Node\Expr\MethodCall || $method_call->var instanceof Node\Expr\FuncCall) {
                yield self::analyzeDecoderTypes($method_call->var, $analyzer);
            }

            MethodCallAnalyzer::analyze(
                statements_analyzer: $analyzer,
                stmt: $method_call,
                context: self::createContext($expr),
                real_method_call: false,
            );
        });

        $analyze_func_call = Option::do(function() use ($expr, $analyzer) {
            $func_call = yield proveOf($expr, Node\Expr\FuncCall::class);

            FunctionCallAnalyzer::analyze(
                statements_analyzer: $analyzer,
                stmt: $func_call,
                context: self::createContext($func_call),
            );

            $upcasted = yield Option::fromNullable($analyzer->node_data->getType($func_call))
                ->flatMap(fn($type) => ClassTypeUpcast::forUnion($type, DecoderInterface::class));

            $analyzer->node_data->setType($func_call, $upcasted);
        });

        return $analyze_method_call->orElse(fn() => $analyze_func_call)->map(fn() => unit());
    }

    private static function createContext(Node\Expr $expr): Context
    {
        $visitor = new class extends NodeVisitorAbstract {
            public Context $context;

            public function __construct()
            {
                $this->context = new Context();
                $this->context->inside_general_use = true;
                $this->context->pure = true;
            }

            public function leaveNode(Node $node): void
            {
                if ($node instanceof Node\Expr\ClassConstFetch && $node->class instanceof Node\Name) {
                    /** @var mixed $resolved_name */
                    $resolved_name = $node->class->getAttribute('resolvedName');

                    if (is_string($resolved_name)) {
                        $this->context->phantom_classes[strtolower($resolved_name)] = true;
                    }
                }
            }
        };

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse([$expr]);

        return $visitor->context;
    }
}
