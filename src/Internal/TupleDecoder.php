<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\Invalid;
use Klimick\Decode\Decoder\Valid;
use function array_map;
use function array_values;
use function implode;
use function is_array;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\invalids;
use function Klimick\Decode\Decoder\valid;

/**
 * @template T
 * @extends AbstractDecoder<list<T>>
 * @psalm-immutable
 */
final class TupleDecoder extends AbstractDecoder
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
        if (!is_array($value)) {
            return invalid($context);
        }

        $errors = [];
        $decodingResults = [];
        $valuesAsList = array_values($value);

        if (count($valuesAsList) !== count($this->innerDecoders)) {
            return invalid($context);
        }

        /** @psalm-suppress MixedAssignment */
        foreach ($valuesAsList as $idx => $valueToDecode) {
            $maybeDecoded = $this->innerDecoders[$idx]
                ->decode($valueToDecode, $context)
                ->get();

            if ($maybeDecoded instanceof Valid) {
                $decodingResults[] = $maybeDecoded->value;
            } else {
                /**
                 * @var $maybeDecoded Invalid
                 * @ignore-var
                 */
                $errors = [...$errors, ...$maybeDecoded->errors];
            }
        }

        if (0 !== count($errors)) {
            return invalids($errors);
        }

        return valid($decodingResults);
    }
}
