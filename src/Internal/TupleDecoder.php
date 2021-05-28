<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\Invalid;
use Klimick\Decode\Decoder\TypeError;
use Klimick\Decode\Decoder\Valid;
use function array_map;
use function array_values;
use function Fp\Evidence\proveTrue;
use function implode;
use function is_array;
use function Klimick\Decode\Decoder\valid;

/**
 * @template T
 * @extends AbstractDecoder<list<T>>
 * @psalm-immutable
 */
class TupleDecoder extends AbstractDecoder
{
    /**
     * @var list<AbstractDecoder<T>>
     */
    public array $innerDecoders;

    /**
     * @param AbstractDecoder<T> $first
     * @param AbstractDecoder<T> ...$rest
     *
     * @no-named-arguments
     */
    public function __construct(AbstractDecoder $first, AbstractDecoder ...$rest)
    {
        $this->innerDecoders = [$first, ...$rest];
    }

    public function name(): string
    {
        $description = implode(', ', array_map(
            fn(AbstractDecoder $inner) => $inner->name(),
            $this->innerDecoders,
        ));

        return "array{{$description}}";
    }

    /**
     * @param mixed $value
     * @param Context $context
     * @return Either<Invalid, Valid<list<T>>>
     */
    public function decode(mixed $value, Context $context): Either
    {
        /**
         * @var callable(): Invalid $toInvalid
         */
        $toInvalid = fn(): Invalid => new Invalid([
            new TypeError($context),
        ]);

        /**
         * @psalm-suppress ImpureMethodCall
         */
        return Either::do(function() use ($value, $context, $toInvalid) {
            yield proveTrue(is_array($value))->toRight(left: $toInvalid);
            $valuesAsList = array_values($value);

            yield proveTrue(count($valuesAsList) === count($this->innerDecoders))->toRight(left: $toInvalid);

            $decodingResults = [];

            /** @psalm-suppress MixedAssignment */
            foreach ($valuesAsList as $idx => $valueToDecode) {
                $decoder = $this->innerDecoders[$idx];

                $decodingResults[] = yield $decoder->decode($valueToDecode, $context);
            }

            return yield valid(array_map(
                fn(Valid $valid) => $valid->value,
                $decodingResults
            ));
        });
    }
}
