<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Fp\Functional\Either\Left;
use Fp\Functional\Either\Right;
use Klimick\Decode\Context;
use Klimick\Decode\AbstractDecoder;
use function Klimick\Decode\invalids;
use function Klimick\Decode\valid;

/**
 * @template T of array
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class IntersectionDecoder extends AbstractDecoder
{
    /** @var non-empty-list<AbstractDecoder<T>> $decoders */
    public array $decoders;

    /**
     * @param AbstractDecoder<T> $first
     * @param AbstractDecoder<T> $second
     * @param AbstractDecoder<T> ...$rest
     *
     * @no-named-arguments
     */
    public function __construct(AbstractDecoder $first, AbstractDecoder $second, AbstractDecoder ...$rest)
    {
        $this->decoders = [$first, $second, ...$rest];
    }

    public function name(): string
    {
        return implode(' & ', array_map(fn($t) => $t->name(), $this->decoders));
    }

    public function decode(mixed $value, Context $context): Either
    {
        $decoded = [];
        $errors = [];

        foreach ($this->decoders as $decoder) {
            $result = $decoder->decode($value, $context->append($decoder->name(), $value));

            if ($result instanceof Left) {
                $errors = [...$errors, ...$result->get()->errors];
            }

            if ($result instanceof Right) {
                $decoded[] = $result->get()->value;
            }
        }

        return match (true) {
            !empty($errors) => invalids($errors),
            !empty($decoded) => valid(array_merge(...$decoded)),
        };
    }
}
