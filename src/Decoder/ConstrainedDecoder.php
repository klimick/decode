<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Error\Context;
use Klimick\Decode\Error\DecodeError;
use function Fp\Collection\flatMap;

/**
 * @template T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class ConstrainedDecoder extends AbstractDecoder
{
    /**
     * @param non-empty-list<ConstraintInterface> $constraints
     * @param DecoderInterface<T> $decoder
     */
    public function __construct(
        public array $constraints,
        public DecoderInterface $decoder,
    ) {}

    public function name(): string
    {
        return $this->decoder->name();
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->decoder
            ->decode($value, $context)
            ->flatMap(function($value) use ($context) {
                if (null === $value) {
                    return Either::right($value);
                }

                $path = $context->path();
                $errors = flatMap($this->constraints, fn($c) => $c->check(Context::root($c, $value, $path), $value));

                return !empty($errors)
                    ? Either::left([
                        DecodeError::constraintErrors($context, $errors)
                    ])
                    : Either::right($value);
            });
    }
}
