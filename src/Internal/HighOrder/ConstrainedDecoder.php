<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\HighOrder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Internal\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder;
use Klimick\Decode\Valid;
use function Klimick\Decode\invalids;
use function Klimick\Decode\valid;

/**
 * @template T
 * @extends Decoder<T>
 * @psalm-immutable
 */
final class ConstrainedDecoder extends Decoder
{
    /**
     * @param Decoder $decoder
     * @param non-empty-list<ConstraintInterface> $constraints
     */
    public function __construct(
        public Decoder $decoder,
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
                    if (!$constraint->isValid($decoded->value)) {
                        $errors[] = $constraint->createError($context, $decoded->value);
                    }
                }

                return !empty($errors)
                    ? invalids($errors)
                    : valid($decoded->value);
            });
    }
}
