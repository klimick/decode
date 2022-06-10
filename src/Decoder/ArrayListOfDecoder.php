<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use function array_is_list;
use function Fp\Collection\keys;

/**
 * @template A
 * @extends AbstractDecoder<list<A>>
 * @psalm-immutable
 */
final class ArrayListOfDecoder extends AbstractDecoder
{
    /**
     * @param DecoderInterface<A> $decoder
     */
    public function __construct(public DecoderInterface $decoder) { }

    public function name(): string
    {
        return "list<{$this->decoder->name()}>";
    }

    public function decode(mixed $value, Context $context): Either
    {
        if (is_array($value)) {
            foreach (keys($value) as $k) {
                if (is_int($k)) continue;
                return invalid($context);
            }
        }

        return arrayOf(int(), $this->decoder)
            ->decode($value, $context)
            ->flatMap(fn($valid) => array_is_list($valid) ? valid($valid) : invalid($context));
    }
}
