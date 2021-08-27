<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder\RuntimeData;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\RuntimeData;
use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\PsalmDecode\Psalm;
use PhpParser\Node;
use Psalm\CodeLocation;
use Psalm\Internal\Type\Comparator\UnionTypeComparator;
use Psalm\Issue\InvalidArgument;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\Type;
use function Fp\Evidence\proveNonEmptyArray;
use function Fp\Evidence\proveNonEmptyString;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;

final class NewAnalysis implements AfterExpressionAnalysisInterface
{
    public static function afterExpressionAnalysis(AfterExpressionAnalysisEvent $event): ?bool
    {
        Option::do(function() use ($event) {
            $new_expr = yield proveOf($event->getExpr(), Node\Expr\New_::class);

            /** @var class-string<RuntimeData> $runtime_data_class */
            $runtime_data_class = yield proveOf($new_expr->class, Node\Name::class)
                ->flatMap(fn($name) => proveString($name->getAttribute('resolvedName')))
                ->filter(fn(string $class) => Psalm::classExtends($class, from: RuntimeData::class, event: $event));

            $expected_constructor_params = yield Option
                ::try(fn() => $runtime_data_class::type())
                ->flatMap(fn($object_decoder) => proveOf($object_decoder, ObjectDecoder::class))
                ->flatMap(fn($object_decoder) => proveNonEmptyArray($object_decoder->shape->decoders))
                ->flatMap(fn($decoders) => self::toConstructor($decoders));

            self::validateArgs($expected_constructor_params, $event, $new_expr);
        });

        return null;
    }

    /**
     * @param non-empty-array<non-empty-string, Type\Union> $decoders
     */
    private static function validateArgs(array $decoders, AfterExpressionAnalysisEvent $event, Node\Expr\New_ $new): void
    {
        $source = $event->getStatementsSource();

        $expected_args_count = count($decoders);
        $actual_args_count = count($new->args);

        if ($expected_args_count !== $actual_args_count) {
            IssueBuffer::accepts(new InvalidArgument(
                message: sprintf("Expected args %s. Actual count %s.", $expected_args_count, $actual_args_count),
                code_location: new CodeLocation($source, $new),
            ));

            return;
        }

        $keys = array_keys($decoders);
        $named_arguments = false;

        foreach ($new->args as $index => $arg_expr) {
            if (null !== $arg_expr->name) {
                $named_arguments = true;
            }

            if ($named_arguments && null === $arg_expr->name) {
                IssueBuffer::accepts(new InvalidArgument(
                    message: 'Positional arguments cannot follows after named arguments',
                    code_location: new CodeLocation($source, $arg_expr),
                ));

                return;
            }

            $arg_name = null !== $arg_expr->name
                ? $arg_expr->name->name
                : $keys[$index];

            $arg_type = Psalm::getType($source->getNodeTypeProvider(), $arg_expr->value);

            if ($arg_type->isNone()) {
                IssueBuffer::accepts(new InvalidArgument(
                    message: sprintf('No type for "%s" arg.', $arg_name),
                    code_location: new CodeLocation($source, $arg_expr),
                ));

                continue;
            }

            if (!array_key_exists($arg_name, $decoders)) {
                IssueBuffer::accepts(new InvalidArgument(
                    message: sprintf('No named argument with name "%s".', $arg_name),
                    code_location: new CodeLocation($source, $arg_expr),
                ));

                continue;
            }

            $expected_arg_type = $decoders[$arg_name];
            $actual_arg_type = $arg_type->get();

            $is_contained = UnionTypeComparator::isContainedBy(
                codebase: $event->getCodebase(),
                input_type: $actual_arg_type,
                container_type: $expected_arg_type,
            );

            if (!$is_contained) {
                $actual_id = $actual_arg_type->getId();
                $expected_id = $expected_arg_type->getId();

                IssueBuffer::accepts(new InvalidArgument(
                    message: sprintf('Invalid type for "%s". Actual: "%s". Expected: "%s".', $arg_name, $actual_id, $expected_id),
                    code_location: new CodeLocation($source, $arg_expr),
                ));
            }
        }
    }

    /**
     * @param non-empty-array<array-key, AbstractDecoder<mixed>> $decoders
     * @return Option<non-empty-array<non-empty-string, Type\Union>>
     */
    private static function toConstructor(array $decoders): Option
    {
        return Option::do(function() use ($decoders) {
            $param_types = [];

            foreach ($decoders as $k => $decoder) {
                $key = yield proveNonEmptyString($k);
                $type = yield Option::try(fn() => Type::parseString($decoder->name()));

                $param_types[$key] = $type;
            }

            return $param_types;
        });
    }
}
