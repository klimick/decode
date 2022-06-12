<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Constraint\Metadata\ConstraintMetaWithPayload;
use Klimick\Decode\Context;

/**
 * @implements ConstraintInterface<string>
 * @psalm-immutable
 */
final class TrimmedConstraint implements ConstraintInterface
{
    public function metadata(): ConstraintMetaWithPayload
    {
        return ConstraintMetaWithPayload::of(
            name: 'TRIMMED',
            payload: [
                'message' => 'Value must not contain leading or trailing whitespaces',
            ],
        );
    }

    public function check(Context $context, mixed $value): iterable
    {
        if (trim($value) === $value) {
            return;
        }

        yield invalid($context);
    }
}
