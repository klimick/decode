<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Collections\Entry;
use Fp\Collections\Map;
use Fp\Functional\Either\Either;
use Fp\Functional\Semigroup\ValidatedSemigroup;
use Fp\Functional\Validated\Validated;
use Klimick\Decode\Internal\HighOrder\HighOrderDecoder;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Decoder\Invalid;
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
     * @param Map<string, AbstractDecoder<TVal>> $decoders
     */
    public function __construct(
        public Map $decoders,
        public bool $partial = false,
    ) { }

    public function name(): string
    {
        $properties = $this->decoders
            ->map(fn($kv) => self::isOptional($this->partial, $kv->value)
                ? "{$kv->key}?: {$kv->value->name()}"
                : "{$kv->key}: {$kv->value->name()}")
            ->values()
            ->toArray();

        return 'array{' . implode(', ', $properties) . '}';
    }

    public function is(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        return $this->decoders->every(
            fn($decoder, $key) => array_key_exists($key, $value) && $decoder->is($value[$key])
        );
    }

    private static function isOptional(bool $partial, AbstractDecoder $decoder): bool
    {
        return $partial || $decoder instanceof HighOrderDecoder && $decoder->isOptional();
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

        return $this->decoders
            ->fold(
                $result,
                /**
                 * @param Validated<Invalid, Valid<array<string, mixed>>> $acc
                 * @param Entry<string, AbstractDecoder> $kv
                 */
                function(Validated $acc, Entry $kv) use ($S, $context, $value) {
                    [$key, $decoder] = $kv->toArray();

                    $decoded = ShapeAccessor
                        ::decodeShapeProperty($context, $decoder, $key, $value)
                        ->toValidated();

                    return $S->combine($acc, $decoded);
                },
            )
            ->toEither();
    }
}
