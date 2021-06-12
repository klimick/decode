<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Either\Either;
use Fp\Functional\Either\Left;
use Klimick\Decode\Decoder\UndefinedError;
use Klimick\Decode\Internal\HighOrder\HighOrderDecoder;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\invalids;
use function Klimick\Decode\Decoder\valid;

/**
 * @template TVal
 * @extends AbstractDecoder<array<string, TVal>>
 * @psalm-immutable
 */
final class ShapeDecoder extends AbstractDecoder
{
    /**
     * @param array<array-key, AbstractDecoder<TVal>> $decoders
     */
    public function __construct(
        public array $decoders,
        public bool $partial = false,
    ) { }

    public function name(): string
    {
        $properties = implode(', ', array_map(
            function(int|string $property, AbstractDecoder $decoder) {
                if ($decoder instanceof HighOrderDecoder && $decoder->isOptional()) {
                    return "{$property}?: {$decoder->name()}";
                }

                return "{$property}: {$decoder->name()}";
            },
            array_keys($this->decoders),
            array_values($this->decoders),
        ));

        return "array{{$properties}}";

    }

    public function decode(mixed $value, Context $context): Either
    {
        if (!is_array($value)) {
            return invalid($context);
        }

        $errors = [];
        $shape = [];

        foreach ($this->decoders as $key => $decoder) {
            /** @var mixed $fromShape */
            $fromShape = ShapeAccessor::access($decoder, $key, $value)->getOrElse(
                fn() => new UndefinedError($context->append($decoder->name(), null, (string) $key))
            );

            if ($fromShape instanceof UndefinedError) {
                if (!$this->partial || ($decoder instanceof HighOrderDecoder && !$decoder->isOptional())) {
                    $errors[] = $fromShape;
                }

                continue;
            }

            $result = $decoder->decode(
                value: $fromShape,
                context: $context->append($decoder->name(), $fromShape, (string) $key),
            );

            if ($result instanceof Left) {
                $errors = [...$errors, ...$result->get()->errors];
                continue;
            }

            /** @var Valid<TVal> $valid */
            $valid = $result->get();

            $shape[(string) $key] = $valid->value;
        }

        return match (true) {
            (!empty($errors)) => invalids($errors),
            (!empty($shape)) => valid($shape),
            ($this->partial && empty($shape)) => valid([]),
        };

    }
}
