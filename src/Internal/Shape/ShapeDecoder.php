<?php /** @noinspection PhpUnusedAliasInspection */

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Closure;
use Fp\Collections\Entry;
use Fp\Collections\Map;
use Fp\Functional\Either\Either;
use Fp\Functional\Semigroup\Semigroup;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Decoder\Invalid;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Internal\Shape\ShapePropertySemigroup;
use Klimick\Decode\DecodeSemigroup;
use Klimick\Decode\Internal\HighOrder\HighOrderDecoder;
use function Klimick\Decode\Decoder\valid;
use function Klimick\Decode\Decoder\invalid;

/**
 * @template TVal
 * @extends AbstractDecoder<array<string, TVal>>
 * @psalm-immutable
 *
 * @psalm-import-type ValidShapeProperties from ShapePropertySemigroup
 * @psalm-type ErrorsOrValidShape = Either<Invalid, ValidShapeProperties>
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

    private static function isOptional(bool $partial, AbstractDecoder $decoder): bool
    {
        return $partial || $decoder instanceof HighOrderDecoder && $decoder->isOptional();
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

    public function decode(mixed $value, Context $context): Either
    {
        return is_array($value)
            ? $this->decoders->fold(valid([]), self::validate($context, $value))
            : invalid($context);
    }

    /**
     * @return Closure(ErrorsOrValidShape, Entry<string, AbstractDecoder>): ErrorsOrValidShape
     * @psalm-pure
     */
    private static function validate(Context $context, array $shape): Closure
    {
        $S = self::shapeSemigroup();

        return fn(Either $errorsOrValidShape, Entry $kv) => $S->combine(
            $errorsOrValidShape,
            ShapeAccessor::decodeProperty($context, $kv->value, $kv->key, $shape),
        );
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
