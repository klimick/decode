<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder\RuntimeData;

use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\UnionRuntimeData;
use Klimick\Decode\Internal\ConstantDecoder;
use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Internal\UnionDecoder;
use Klimick\PsalmDecode\Issue\UnionRuntimeData\InvalidMatcherTypeIssue;
use Klimick\PsalmDecode\Issue\UnionRuntimeData\UnexhaustiveMatchIssue;
use Klimick\PsalmDecode\Psalm;
use PhpParser\Node;
use Fp\Functional\Option\Option;
use Psalm\Codebase;
use Psalm\CodeLocation;
use Psalm\Internal\Type\Comparator\UnionTypeComparator;
use Psalm\IssueBuffer;
use Psalm\NodeTypeProvider;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\StatementsSource;
use Psalm\Storage\FunctionLikeParameter;
use Psalm\Type;
use Psalm\Type\Atomic\TNamedObject;
use function Fp\Collection\at;
use function Fp\Collection\first;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;
use function Fp\Evidence\proveTrue;

final class UnionMatchAnalysis implements AfterExpressionAnalysisInterface
{
    public static function afterExpressionAnalysis(AfterExpressionAnalysisEvent $event): ?bool
    {
        Option::do(function() use ($event) {
            $codebase = $event->getCodebase();
            $source = $event->getStatementsSource();
            $type_provider = $source->getNodeTypeProvider();

            $match_call = yield proveOf($event->getExpr(), Node\Expr\MethodCall::class);
            $match_call_id = yield proveOf($match_call->name, Node\Identifier::class);

            yield proveTrue('match' === $match_call_id->name);

            $union_runtime_data_class = yield Psalm::getType($type_provider, $match_call->var)
                ->flatMap(fn($union) => Psalm::asSingleAtomic($union))
                ->filter(fn($atomic) => $atomic instanceof TNamedObject)
                ->map(fn($atomic) => $atomic->value)
                ->filter(fn($class) => class_exists($class))
                ->filter(fn($class) => is_subclass_of($class, UnionRuntimeData::class));

            $actual_match_arg_nodes = yield self::getActualMatchArgNodes($match_call);
            $required_match_args = yield self::getRequiredMatchArgs($union_runtime_data_class);

            self::analyzeMatch(
                $codebase,
                $source,
                $type_provider,
                $match_call,
                $required_match_args,
                $actual_match_arg_nodes,
            );
        });

        return null;
    }

    /**
     * @param array<string, Type\Union> $required_match_args
     * @param array<string, Node\Expr> $actual_match_arg_nodes
     */
    private static function analyzeMatch(
        Codebase $codebase,
        StatementsSource $source,
        NodeTypeProvider $type_provider,
        Node\Expr\MethodCall $method_call,
        array $required_match_args,
        array $actual_match_arg_nodes,
    ): void
    {
        Option::do(function() use ($codebase, $source, $type_provider, $method_call, $required_match_args, $actual_match_arg_nodes) {
            $matcher_return_types = [];

            foreach ($required_match_args as $required_matcher_name => $required_matcher_param_type) {
                // Matcher missing in the match call
                if (!array_key_exists($required_matcher_name, $actual_match_arg_nodes)) {
                    $code_location = new CodeLocation($source, $method_call);
                    $issue = new UnexhaustiveMatchIssue($required_matcher_name, $code_location);

                    IssueBuffer::accepts($issue, $source->getSuppressedIssues());

                    continue;
                }

                $actual_marcher_arg_expr = $actual_match_arg_nodes[$required_matcher_name];
                $actual_marcher_arg_type = yield Psalm::getType($type_provider, $actual_marcher_arg_expr);

                $actual_matcher_fn_arg = self::getMatcherFnAtomic($actual_marcher_arg_type);

                // User can specify matcher with more than one parameter
                if (null === $actual_matcher_fn_arg || count($actual_matcher_fn_arg->params ?? []) > 1) {
                    $code_location = new CodeLocation($source, $actual_marcher_arg_expr);
                    $issue = new InvalidMatcherTypeIssue($required_matcher_param_type, $actual_marcher_arg_type, $code_location);

                    IssueBuffer::accepts($issue, $source->getSuppressedIssues());

                    continue;
                }

                // Save matcher return type for future the match call type inference
                if (null !== $actual_matcher_fn_arg->return_type) {
                    $matcher_return_types[] = $actual_matcher_fn_arg->return_type;
                }

                $actual_matcher_param = first($actual_matcher_fn_arg->params ?? [])->get() ?? null;

                // ok callable can be with zero params
                if (null === $actual_matcher_param) {
                    continue;
                }

                // Complete matcher parameter type (todo: does not work as expect)
                if (null === $actual_matcher_param->type) {
                    self::completeMatcherParamType(
                        $actual_matcher_param,
                        $required_matcher_param_type,
                        $actual_matcher_fn_arg->return_type,
                        $actual_marcher_arg_expr,
                        $type_provider,
                    );

                    continue;
                }

                // Compare expected matched type with actual
                if (!UnionTypeComparator::isContainedBy($codebase, $required_matcher_param_type, $actual_matcher_param->type)) {
                    $code_location = new CodeLocation($source, $actual_marcher_arg_expr);
                    $issue = new InvalidMatcherTypeIssue($required_matcher_param_type, $actual_marcher_arg_type, $code_location);

                    IssueBuffer::accepts($issue, $source->getSuppressedIssues());

                    continue;
                }
            }

            if (!empty($matcher_return_types)) {
                $match_return_type = Type::combineUnionTypeArray($matcher_return_types, $codebase);
                $type_provider->setType($method_call, $match_return_type);
            }
        });
    }

    private static function getMatcherFnAtomic(Type\Union $actual_arg_type): null|Type\Atomic\TClosure|Type\Atomic\TCallable
    {
        return Psalm::asSingleAtomic($actual_arg_type)
            ->filter(fn($a) => $a instanceof Type\Atomic\TClosure || $a instanceof Type\Atomic\TCallable)
            ->get();
    }

    /**
     * @return Option<array<string, Node\Expr>>
     */
    private static function getActualMatchArgNodes(Node\Expr\MethodCall $method_call): Option
    {
        return Option::do(function() use ($method_call) {
            $match_args = [];

            foreach ($method_call->args as $arg) {
                $matcher_name = yield proveOf($arg->name, Node\Identifier::class)->map(fn($id) => $id->name);
                $match_args[$matcher_name] = $arg->value;
            }

            return $match_args;
        });
    }

    /**
     * @param class-string<UnionRuntimeData> $union_runtime_data_class
     * @return Option<array<string, Type\Union>>
     */
    private static function getRequiredMatchArgs(string $union_runtime_data_class): Option
    {
        return Option::do(function() use ($union_runtime_data_class) {
            $union_decoder = yield Option::try(fn() => $union_runtime_data_class::type())
                ->flatMap(fn($decoder) => proveOf($decoder, UnionDecoder::class));

            $required_args = [];

            foreach ($union_decoder->decoders as $decoder) {
                yield proveTrue($decoder instanceof ObjectDecoder);

                $matcher_name = yield at($decoder->decoders, 'type')
                    ->flatMap(fn($d) => proveOf($d, ConstantDecoder::class))
                    ->flatMap(fn($d) => proveString($d->constant));

                $matcher_param_type = yield at($decoder->decoders, 'instance')
                    ->flatMap(fn($d) => proveOf($d, AbstractDecoder::class))
                    ->map(fn($d) => $d->name())
                    ->flatMap(fn($name) => Option::try(
                        fn() => Type::parseString($name)
                    ));

                $required_args[$matcher_name] = $matcher_param_type;
            }

            return $required_args;
        });
    }

    private static function completeMatcherParamType(
        FunctionLikeParameter $current_matcher_param,
        Type\Union $expected_matcher_param_type,
        Type\Union|null $inferred_matcher_return_type,
        Node\Expr $matcher_arg_node,
        NodeTypeProvider $type_provider,
    ): void
    {
        $inferred_type = new Type\Union([
            new Type\Atomic\TCallable(
                params: [
                    new FunctionLikeParameter(
                        name: $current_matcher_param->name,
                        by_ref: $current_matcher_param->by_ref,
                        type: $expected_matcher_param_type,
                    ),
                ],
                return_type: $inferred_matcher_return_type,
            )
        ]);

        $type_provider->setType($matcher_arg_node, $inferred_type);

        if (null !== $current_matcher_param->location) {
            IssueBuffer::remove(
                file_path: $current_matcher_param->location->file_path,
                issue_type: 'MissingClosureParamType',
                file_offset: $current_matcher_param->location->raw_file_start,
            );
        }
    }
}
