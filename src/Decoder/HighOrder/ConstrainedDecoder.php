<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder\HighOrder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use Klimick\Decode\ContextEntry;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\Error\ConstraintsError;

/**
 * @template T
 * @extends HighOrderDecoder<T>
 * @psalm-immutable
 */
final class ConstrainedDecoder extends HighOrderDecoder
{
    /**
     * @param DecoderInterface<T> $decoder
     * @param non-empty-list<ConstraintInterface> $constraints
     */
    public function __construct(public array $constraints, DecoderInterface $decoder)
    {
        parent::__construct($decoder);
    }

    public function isConstrained(): bool
    {
        return true;
    }

    public function asConstrained(): ?ConstrainedDecoder
    {
        return $this;
    }

    public function name(): string
    {
        return $this->decoder->name();
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->decoder
            ->decode($value, $context)
            ->flatMap(function($decoded) use ($context) {
                if (null === $decoded) {
                    return Either::right($decoded);
                }

                $errors = [];

                foreach ($this->constraints as $constraint) {
                    $constraintCtx = new Context([
                        new ContextEntry($constraint->name(), $decoded, $context->path()),
                    ]);

                    foreach ($constraint->check($constraintCtx, $decoded) as $error) {
                        $errors[] = $error;
                    }
                }

                return !empty($errors)
                    ? Either::left([
                        new ConstraintsError($errors),
                    ])
                    : Either::right($decoded);
            });
    }
}
