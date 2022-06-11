<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\Error\TypeError;
use function array_key_last;
use function is_array;
use function is_int;

/**
 * @template TKey of array-key
 * @template TVal
 * @extends AbstractDecoder<array<TKey, TVal>>
 * @psalm-immutable
 */
final class ArrayOfDecoder extends AbstractDecoder
{
    /**
     * @param DecoderInterface<TKey> $key
     * @param DecoderInterface<TVal> $value
     */
    public function __construct(
        public DecoderInterface $key,
        public DecoderInterface $value,
    ) { }

    public function name(): string
    {
        return "array<{$this->key->name()}, {$this->value->name()}>";
    }

    public function decode(mixed $value, Context $context): Either
    {
        if (!is_array($value)) {
            return invalid($context);
        }

        $decoded = [];
        $errors = [];

        $checkIntToStringCoercion = $this->key instanceof StringDecoder;
        $keyHasAliases = !empty($this->key->getAliases());
        $valueHasAliases = !empty($this->value->getAliases());

        /** @psalm-suppress MixedAssignment */
        foreach ($value as $k => $v) {
            $decodedK = $keyHasAliases
                ? ShapeAccessor::decodeProperty($context, $this->key, $k, $v)
                : $this->key->decode($k, $context($this->key, $k, $k));

            $decodedV = $valueHasAliases
                ? ShapeAccessor::decodeProperty($context, $this->value, $k, $v)
                : $this->value->decode($v, $context($this->value, $v, $k));

            if ($decodedV->isLeft()) {
                $errors[] = $decodedV->get();
            }

            if ($decodedK->isLeft()) {
                $errors[] = $decodedK->get();
            }

            if ($decodedK->isRight() && $decodedV->isRight()) {
                $decoded[$decodedK->get()] = $decodedV->get();

                // PHP don't see difference between '1' and 1 for array key.
                if ($checkIntToStringCoercion && is_int(array_key_last($decoded))) {
                    $errors[] = [
                        new TypeError($context($this->key, (int) $k, $k)),
                    ];
                }
            }
        }

        return 0 !== count($errors) ? invalids($errors) : valid($decoded);
    }
}
