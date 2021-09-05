<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\arr;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\valid;

/**
 * @template A
 * @extends AbstractDecoder<list<A>>
 * @psalm-immutable
 */
final class ArrListDecoder extends AbstractDecoder
{
    /**
     * @param AbstractDecoder<A> $decoder
     */
    public function __construct(public AbstractDecoder $decoder) { }

    public function name(): string
    {
        return "list<{$this->decoder->name()}>";
    }

    public function is(mixed $value): bool
    {
        return arr(int(), $this->decoder)->is($value) && self::isList($value);
    }

    /**
     * @template T
     * @psalm-pure
     *
     * @param array<array-key, T> $arr
     * @psalm-assert-if-true list<T> $arr
     */
    public static function isList(array $arr): bool
    {
        $count = count($arr);

        return 0 === $count || array_keys($arr) === range(0, $count - 1);
    }

    public function decode(mixed $value, Context $context): Either
    {
        if (is_array($value)) {
            foreach (array_keys($value) as $k) {
                if (is_int($k)) continue;
                return invalid($context);
            }
        }

        return arr(int(), $this->decoder)
            ->decode($value, $context)
            ->flatMap(fn(Valid $valid) => self::isList($valid->value)
                ? valid($valid->value)
                : invalid($context)
            );
    }
}
