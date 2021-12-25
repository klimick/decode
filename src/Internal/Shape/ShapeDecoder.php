<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Either\Either;
use Fp\Functional\Semigroup\Semigroup;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Decoder\Invalid;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\DecodeSemigroup;
use Klimick\Decode\Internal\HighOrder\HighOrderDecoder;
use function Fp\Collection\every;
use function Klimick\Decode\Decoder\valid;
use function Klimick\Decode\Decoder\invalid;

/**
 * @template-covariant TShape of array
 * @extends AbstractDecoder<TShape>
 * @psalm-immutable
 *
 * @psalm-import-type ValidShapeProperties from ShapePropertySemigroup
 * @psalm-type ErrorsOrValidShape = Either<Invalid, ValidShapeProperties>
 */
final class ShapeDecoder extends AbstractDecoder
{
    /**
     * @param array<string, DecoderInterface> $decoders
     */
    public function __construct(
        public array $decoders,
        public bool $partial = false,
    ) { }

    public function name(): string
    {
        $properties = implode(', ', array_map(
            function(int|string $property, DecoderInterface $decoder) {
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
        return is_array($value) && every(
            $this->decoders,
            fn(DecoderInterface $d, string $key) => array_key_exists($key, $value) && $d->is($value[$key])
        );
    }

    public function decode(mixed $value, Context $context): Either
    {
        if (!is_array($value)) {
            return invalid($context);
        }

        $decodedShape = valid([]);
        $S = self::shapeSemigroup();

        foreach ($this->decoders as $key => $decoder) {
            $decodedKV = ShapeAccessor::decodeProperty($context, $decoder, $key, $value, $this->partial);
            $decodedShape = $S->combine($decodedShape, $decodedKV);
        }

        /** @var Either<Invalid, Valid<TShape>> */
        return $decodedShape;
    }

    /**
     * @return Semigroup<Either<Invalid, ValidShapeProperties>>
     * @psalm-pure
     */
    public static function shapeSemigroup(): Semigroup
    {
        return new DecodeSemigroup(
            new ShapePropertySemigroup(),
        );
    }
}
