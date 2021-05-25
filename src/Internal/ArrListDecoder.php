<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Typed as t;
use Klimick\Decode\Valid;
use Klimick\Decode\Context;
use Klimick\Decode\AbstractDecoder;
use function Klimick\Decode\arr;
use function Klimick\Decode\invalid;
use function Klimick\Decode\valid;

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

    public function decode(mixed $value, Context $context): Either
    {
        return arr(t::int, $this->decoder)
            ->decode($value, $context)
            ->flatMap(function(Valid $valid) use ($context) {
                $count = count($valid->value);

                return (0 === $count || array_keys($valid->value) === range(0, $count - 1))
                    ? valid(array_values($valid->value))
                    : invalid($context);
            });
    }
}
