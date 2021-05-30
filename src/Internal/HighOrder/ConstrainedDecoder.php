<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\HighOrder;

use Fp\Functional\Either\Either;
use Fp\Functional\Either\Left;
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

    public function decode(mixed $value, Context $context): Either
    {
        return $this->decoder
            ->decode($value, $context)
            ->flatMap(function(Valid $decoded) {
                if (null === $decoded->value) {
                    return valid($decoded->value);
                }

                $errors = [];

                foreach ($this->constraints as $constraint) {
                    $context = new Context([
                        new ContextEntry($constraint->name(), $decoded->value)
                    ]);

                    $result = $constraint->check($context, $decoded->value);

                    if ($result instanceof Left) {
                        $errors = [...$errors, ...$result->get()->errors];
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
