<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\arrList;
use function Klimick\Decode\invalid;
use function Klimick\Decode\valid;

/**
 * @template T
 * @extends AbstractDecoder<non-empty-list<T>>
 * @psalm-immutable
 */
final class NonEmptyArrListDecoder extends AbstractDecoder
{
    /**
     * @param AbstractDecoder<T> $decoder
     */
    public function __construct(public AbstractDecoder $decoder) { }

    public function name(): string
    {
        return "non-empty-list<{$this->decoder->name()}>";
    }

    public function decode(mixed $value, Context $context): Either
    {
        return arrList($this->decoder)
            ->decode($value, $context)
            ->flatMap(
                fn(Valid $valid) => !empty($valid->value)
                    ? valid($valid->value)
                    : invalid($context)
            );
    }
}
