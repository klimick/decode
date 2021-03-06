<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Error\Context;
use Klimick\Decode\Error\DecodeError;
use function array_key_last;
use function is_array;
use function is_int;
use function Klimick\Decode\Utils\getAliasedTypename;

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

        $keyTypename = mixed()->rename(getAliasedTypename($this->key));
        $valueTypename = mixed()->rename(getAliasedTypename($this->value));

        /** @psalm-suppress MixedAssignment */
        foreach ($value as $k => $v) {
            $decodedK = $keyHasAliases
                ? ShapeAccessor::decodeProperty($context, $this->key, $k, $v)
                : $this->key->decode($k, $context($this->key, $k, $k));

            $decodedV = $valueHasAliases
                ? ShapeAccessor::decodeProperty($context, $this->value, $k, $v)
                : $this->value->decode($v, $context($this->value, $v, $k));

            if ($decodedK->isLeft()) {
                $keyErrors = $decodedK->get();

                $errors[] = $keyHasAliases && ShapeAccessor::isUndefined($keyErrors)
                    ? [DecodeError::typeError($context($keyTypename, $v, $k))]
                    : $keyErrors;
            }

            if ($decodedV->isLeft()) {
                $valueErrors = $decodedV->get();

                $errors[] = $valueHasAliases && ShapeAccessor::isUndefined($valueErrors)
                    ? [DecodeError::typeError($context($valueTypename, $v, $k))]
                    : $valueErrors;
            }

            if ($decodedK->isRight() && $decodedV->isRight()) {
                $decoded[$decodedK->get()] = $decodedV->get();

                // PHP don't see difference between '1' and 1 for array key.
                $lastKey = array_key_last($decoded);

                if ($checkIntToStringCoercion && is_int($lastKey)) {
                    $errors[] = [
                        DecodeError::typeError($context($this->key, $lastKey, $k)),
                    ];
                }
            }
        }

        return 0 !== count($errors) ? invalids($errors) : valid($decoded);
    }
}
