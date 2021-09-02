<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Either\Either;
use Fp\Functional\Semigroup\ValidatedSemigroup;
use Fp\Functional\Validated\Validated;
use Klimick\Decode\Internal\HighOrder\HighOrderDecoder;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\invalid;

/**
 * @template TVal
 * @extends AbstractDecoder<array<string, TVal>>
 * @psalm-immutable
 */
final class ShapeDecoder extends AbstractDecoder
{
    /**
     * @param array<string, AbstractDecoder<TVal>> $decoders
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

    public function is(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach (array_keys($this->decoders) as $k) {
            if (!array_key_exists($k, $value) || !$this->decoders[$k]->is($value[$k])) {
                return false;
            }
        }

        return true;
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

        $S = new ValidatedSemigroup(
            new ShapePropertySemigroup(),
            new ShapeErrorSemigroup(),
        );

        $result = Validated::valid(new Valid([]));

        foreach ($this->decoders as $key => $decoder) {
            $result = $S->combine(
                $result,
                ShapeAccessor::decodeShapeProperty($context, $decoder, $key, $value)->toValidated()
            );
        }

        return $result->toEither();
    }
}
