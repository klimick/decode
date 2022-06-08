<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use Klimick\Decode\ContextEntry;
use Klimick\Decode\Decoder\Error\ConstraintsError;

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
