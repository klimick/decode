<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\HighOrder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\ConstraintError;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\ConstraintsError;
use Klimick\Decode\Decoder\Valid;
use function Klimick\Decode\Decoder\invalids;
use function Klimick\Decode\Decoder\valid;

/**
 * @template T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class ConstrainedDecoder extends AbstractDecoder
{
    /**
     * @param AbstractDecoder $decoder
     * @param non-empty-list<ConstraintInterface> $constraints
     */
    public function __construct(
        public AbstractDecoder $decoder,
        public array $constraints
    ) { }

    public function name(): string
    {
        return $this->decoder->name();
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
                    if (!$constraint->check($context, $decoded->value)) {
                        $errors[] = new ConstraintError($context, 'foo', []);
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