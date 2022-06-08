<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\MethodReturnTypeProvider;

use Fp\Collections\NonEmptyArrayList;
use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\CallArg;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\ShapeDecoder;
use Klimick\PsalmDecode\Common\DecoderType;
use Klimick\PsalmDecode\Issue;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Union;
use function array_key_exists;
use function Fp\Collection\everyMap;
use function Fp\Collection\filter;
use function Fp\Collection\first;
use function Fp\Evidence\proveNonEmptyArray;
use function in_array;

final class ShapePickOmitMethodReturnTypeProvider implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [ShapeDecoder::class];
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Union
    {
        $type = Option::do(function() use ($event) {
            $method = yield Option::some($event->getMethodNameLowercase())
                ->filter(fn($name) => $name === 'pick' || $name === 'omit');

            $shape = yield Option::fromNullable($event->getTemplateTypeParameters())
                ->flatMap(fn(array $templates) => first($templates))
                ->flatMap(fn(Union $template) => PsalmApi::$types->asSingleAtomicOf(TKeyedArray::class, $template));

            $props = yield PsalmApi::$args->getNonEmptyCallArgs($event)
                ->map(fn(NonEmptyArrayList $args) => $args->head())
                ->flatMap(fn(CallArg $arg) => PsalmApi::$types->asSingleAtomicOf(TKeyedArray::class, $arg->type))
                ->flatMap(
                    fn(TKeyedArray $pick) => everyMap($pick->properties, fn(Union $literal) => PsalmApi::$types
                        ->asSingleAtomicOf(TLiteralString::class, $literal)
                        ->map(fn(TLiteralString $string) => $string->value))
                );

            $undefined = filter(
                collection: $props,
                predicate: fn($prop) => !array_key_exists($prop, $shape->properties),
            );

            if (!empty($undefined)) {
                $source = $event->getSource();
                $code_location = $event->getCodeLocation();

                IssueBuffer::accepts(
                    e: new Issue\UndefinedShapeProperty($shape, $undefined, $code_location),
                    suppressed_issues: $source->getSuppressedIssues(),
                );
            }

            $filtered_shape = yield proveNonEmptyArray(filter(
                collection: $shape->properties,
                predicate: match ($method) {
                    'pick' => fn(Union $_, int|string $prop): bool => in_array($prop, $props),
                    'omit' => fn(Union $_, int|string $prop): bool => !in_array($prop, $props),
                },
                preserveKeys: true,
            ));

            return DecoderType::createShape($filtered_shape);
        });

        return $type->get();
    }
}
