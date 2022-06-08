<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\Error\TypeError;

/**
 * @template TLeft
 * @template TRight
 * @extends AbstractDecoder<Either<TLeft, TRight>>
 * @psalm-immutable
 */
final class EitherDecoder extends AbstractDecoder
{
    /**
     * @param DecoderInterface<TLeft> $left
     * @param DecoderInterface<TRight> $right
     */
    public function __construct(
        private DecoderInterface $left,
        private DecoderInterface $right,
    ) {}

    public function name(): string
    {
        return "Either<{$this->left->name()}, {$this->right->name()}>";
    }

    public function decode(mixed $value, Context $context): Either
    {
        $left = $this->left
            ->decode($value, $context)
            ->map(fn($decoded) => Either::left($decoded));

        $right = fn(): Either => $this->right
            ->decode($value, $context)
            ->map(fn($decoded) => Either::right($decoded));

        return $left->orElse($right)->mapLeft(fn() => [
            new TypeError($context($this->name(), $value)),
        ]);
    }
}
