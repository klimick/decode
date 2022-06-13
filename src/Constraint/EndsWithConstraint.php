<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Constraint\Metadata\ConstraintMetaWithPayload;
use Klimick\Decode\Error\Context;

/**
 * @implements ConstraintInterface<string>
 * @psalm-immutable
 */
final class EndsWithConstraint implements ConstraintInterface
{
    /**
     * @param non-empty-string $value
     */
    public function __construct(
        public string $value,
    ) {}

    public function metadata(): ConstraintMetaWithPayload
    {
        return ConstraintMetaWithPayload::of(
            name: 'ENDS_WITH',
            payload: [
                'mustEndsWith' => $this->value,
            ],
        );
    }

    public function check(Context $context, mixed $value): iterable
    {
        if (str_ends_with($value, $this->value)) {
            return;
        }

        yield invalid($context);
    }
}
