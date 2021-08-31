<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\Invalid;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\invalids;
use function Klimick\Decode\Decoder\mixed;
use function Klimick\Decode\Decoder\nonEmptyArrList;
use function Klimick\Decode\Decoder\valid;

/**
 * @template T
 * @extends AbstractDecoder<list<T>>
 * @psalm-immutable
 */
final class TupleDecoder extends AbstractDecoder
{
    /**
     * @var non-empty-list<AbstractDecoder<T>>
     */
    public array $decoders;

    /**
     * @param AbstractDecoder<T> $first
     * @param AbstractDecoder<T> ...$rest
     *
     * @no-named-arguments
     */
    public function __construct(AbstractDecoder $first, AbstractDecoder ...$rest)
    {
        $this->decoders = [$first, ...$rest];
    }

    public function name(): string
    {
        $types = implode(', ', array_map(
            fn(AbstractDecoder $decoder) => $decoder->name(),
            $this->decoders,
        ));

        return "array{{$types}}";
    }

    public function is(mixed $value): bool
    {
        if (!is_array($value) || !ArrListDecoder::isList($value) || count($value) !== count($this->decoders)) {
            return false;
        }

        foreach ($this->decoders as $index => $decoder) {
            if (!$decoder->is($value[$index])) {
                return false;
            }
        }

        return true;
    }

    public function decode(mixed $value, Context $context): Either
    {
        return nonEmptyArrList(mixed())
            ->decode($value, $context)
            ->map(fn($valid) => $valid->value)
            ->flatMap(function($list) use ($context) {
                if (count($list) !== count($this->decoders)) {
                    return invalid($context);
                }

                $decoded = [];
                $errors = [];

                /** @psalm-var mixed $v */
                foreach ($list as $k => $v) {
                    $result = $this->decoders[$k]
                        ->decode($v, $context($this->decoders[$k]->name(), $v, (string) $k))
                        ->get();

                    if ($result instanceof Invalid) {
                        $errors = [...$errors, ...$result->errors];
                    } else {
                        $decoded[] = $result->value;
                    }
                }

                return 0 !== count($errors) ? invalids($errors) : valid($decoded);
            });
    }
}
