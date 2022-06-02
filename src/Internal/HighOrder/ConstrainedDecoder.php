<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\HighOrder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\Invalid;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use Klimick\Decode\ContextEntry;
use Klimick\Decode\Decoder\ConstraintsError;
use Klimick\Decode\Decoder\DecoderInterface;
use function Klimick\Decode\Decoder\invalids;
use function Klimick\Decode\Decoder\valid;

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
                    return valid($decoded);
                }

                $errors = [];

                foreach ($this->constraints as $constraint) {
                    $constraintContext = new Context([
                        new ContextEntry(
                            name: $constraint->name(),
                            actual: $decoded,
                            key: $context->path(),
                        ),
                    ]);

                    $result = $constraint
                        ->check($constraintContext, $decoded)
                        ->get();

                    if ($result instanceof Invalid) {
                        $errors = [...$errors, ...$result->errors];
                    }
                }

                return !empty($errors)
                    ? invalids([
                        new ConstraintsError($errors)
                    ])
                    : valid($decoded);
            });
    }
}
