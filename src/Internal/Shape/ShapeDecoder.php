<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Either\Either;
use Klimick\Decode\Decoder\UndefinedError;
use Klimick\Decode\Internal\HighOrder\HighOrderDecoder;
use Klimick\Decode\Decoder\Invalid;
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

    private function isOptional(AbstractDecoder $decoder): bool
    {
        return $this->partial || $decoder instanceof HighOrderDecoder && $decoder->isOptional();
    }

    public function decode(mixed $value, Context $context): Either
    {
        if (!is_array($value)) {
            return invalid($context);
        }

        $errors = [];
        $shape = [];

        foreach ($this->decoders as $key => $decoder) {
            /** @psalm-suppress MixedAssignment */
            $fromShape = ShapeAccessor::access($decoder, $key, $value)
                ->getOrElse(fn() => new UndefinedError(
                    $context->append(
                        name: $decoder->name(),
                        actual: null,
                        key: (string) $key,
                    )
                ));

            if ($fromShape instanceof UndefinedError) {
                if (!$this->isOptional($decoder)) {
                    $errors[] = $fromShape;
                }

                continue;
            }

            $result = $decoder
                ->decode($fromShape, $context->append($decoder->name(), $fromShape, (string) $key))
                ->get();

            if ($result instanceof Invalid) {
                $errors = [...$errors, ...$result->errors];
            } else {
                $shape[(string) $key] = $result->value;
            }
        }

        return match (true) {
            (!empty($errors)) => invalids($errors),
            (!empty($shape)) => valid($shape),
            ($this->partial && empty($shape)) => valid([]),
        };

    }
}
