<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\DecoderInterface;
use function Fp\Collection\map;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\invalids;
use function Klimick\Decode\Decoder\mixed;
use function Klimick\Decode\Decoder\nonEmptyListOf;
use function Klimick\Decode\Decoder\valid;

/**
 * @template T
 * @extends AbstractDecoder<list<T>>
 * @psalm-immutable
 */
final class TupleDecoder extends AbstractDecoder
{
    /**
     * @param array<int, DecoderInterface<T>> $decoders
     */
    public function __construct(private array $decoders) { }

    public function name(): string
    {
        $types = map($this->decoders, fn(DecoderInterface $d) => $d->name());

        return 'array{' . implode(', ', $types) . '}';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return nonEmptyListOf(mixed())
            ->decode($value, $context)
            ->flatMap(function($tuple) use ($context) {
                if (count($tuple) !== count($this->decoders)) {
                    return invalid($context);
                }

                $decoded = [];
                $errors = [];

                foreach ($this->decoders as $k => $decoder) {
                    $result = $decoder->decode($tuple[$k], $context($decoder->name(), $tuple[$k], (string) $k));

                    if ($result->isLeft()) {
                        $errors[] = $result->get();
                    } else {
                        $decoded[] = $result->get();
                    }
                }

                return 0 !== count($errors) ? invalids($errors) : valid($decoded);
            });
    }
}
