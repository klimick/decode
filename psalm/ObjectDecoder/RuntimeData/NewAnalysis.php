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
        $code_location = new CodeLocation($source, $new);

        if (count($decoders) !== count($new->args)) {
            IssueBuffer::accepts(new InvalidArgument('Not all args passed', $code_location));

            return;
        }

        $keys = array_keys($decoders);

        foreach ($new->args as $index => $arg) {
            if (null !== $arg->name) {
                IssueBuffer::accepts(new InvalidArgument('Named params not allowed yet', $code_location));

                continue;
            }

            $type = Psalm::getType($source->getNodeTypeProvider(), $arg->value);

            if ($type->isNone()) {
                IssueBuffer::accepts(new InvalidArgument("No type for '{$keys[$index]}'", $code_location));

                continue;
            }

            $actual_arg_type = $type->get();
            $expected_arg_type = $decoders[$keys[$index]];

            $is_contained = UnionTypeComparator::isContainedBy(
                $event->getCodebase(),
                input_type: $actual_arg_type,
                container_type: $expected_arg_type,
            );

            if (!$is_contained) {
                IssueBuffer::accepts(new InvalidArgument(
                    message: implode(' ', [
                        "Invalid type for '{$keys[$index]}'.",
                        "Actual: {$actual_arg_type->getId()}.",
                        "Expected: {$expected_arg_type->getId()}",
                    ]),
                    code_location: $code_location,
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
