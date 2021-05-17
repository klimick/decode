<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Either\Either;
use Fp\Functional\Either\Left;
use Klimick\Decode\Valid;
use Klimick\Decode\Context;
use Klimick\Decode\DecoderInterface;
use function Klimick\Decode\invalid;
use function Klimick\Decode\invalids;
use function Klimick\Decode\valid;

/**
 * @template TVal
 * @implements DecoderInterface<array<string, TVal>>
 * @psalm-immutable
 */
final class ShapeDecoder implements DecoderInterface
{
    /**
     * @param array<array-key, DecoderInterface<TVal>> $decoders
     */
    public function __construct(
        public array $decoders,
        public bool $partial = false,
    ) { }

    public function name(): string
    {
        $properties = implode(', ', array_map(
            fn(int|string $property, DecoderInterface $decoder) => "{$property}: {$decoder->name()}",
            array_keys($this->decoders),
            array_values($this->decoders),
        ));

        return "array{{$properties}}";

    }

    public function decode(mixed $value, Context $context): Either
    {
        if (!is_array($value)) {
            return invalid($context);
        }

        $errors = [];
        $shape = [];

        foreach ($this->decoders as $key => $decoder) {
            /** @var mixed $fromShape */
            $fromShape = ShapeAccessor::access($decoder, $key, $value);

            if ($fromShape instanceof UndefinedProperty && $this->partial) {
                continue;
            }

            $result = $decoder->decode(
                value: $fromShape,
                context: $context->append($decoder->name(), $fromShape, (string) $key),
            );

            if ($result instanceof Left) {
                $errors = [...$errors, ...$result->get()->errors];
                continue;
            }

            /** @var Valid<TVal> $valid */
            $valid = $result->get();

            $shape[(string) $key] = $valid->value;
        }

        return match (true) {
            (!empty($errors)) => invalids($errors),
            (!empty($shape)) => valid($shape),
            ($this->partial && empty($shape)) => valid([]),
        };

    }
}
