<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\HighOrder;

use Fp\Functional\Either\Either;
use Fp\Functional\Either\Left;
use Klimick\Decode\Constraint\Invalid;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use Klimick\Decode\ContextEntry;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\ConstraintsError;
use Klimick\Decode\Decoder\Valid;
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
     * @param AbstractDecoder $decoder
     * @param non-empty-list<ConstraintInterface> $constraints
     */
    public function __construct(public array $constraints, AbstractDecoder $decoder)
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

    public function is(mixed $value): bool
    {
        if (!$this->decoder->is($value)) {
            return false;
        }

        foreach ($this->constraints as $constraint) {
            $context = Context::root($constraint->name(), $value);

            if ($constraint->check($context, $value) instanceof Left) {
                return false;
            }
        }

        return true;
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->decoder
            ->decode($value, $context)
            ->flatMap(function(Valid $decoded) use ($context) {
                if (null === $decoded->value) {
                    return valid($decoded->value);
                }

                $errors = [];

                foreach ($this->constraints as $constraint) {
                    $constraintContext = new Context([
                        new ContextEntry(
                            name: $constraint->name(),
                            actual: $decoded->value,
                            key: $context->path(),
                        ),
                    ]);

                    $result = $constraint
                        ->check($constraintContext, $decoded->value)
                        ->get();

                    if ($result instanceof Invalid) {
                        $errors = [...$errors, ...$result->errors];
                    }
                }

                return !empty($errors)
                    ? invalids([
                        new ConstraintsError($errors)
                    ])
                    : valid($decoded->value);
            });
    }
}
