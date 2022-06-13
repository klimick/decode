<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Constraint\Metadata\ConstraintMetaWithPayload;
use Klimick\Decode\Error\Context;

/**
 * @implements ConstraintInterface<string>
 * @psalm-immutable
 */
final class UuidConstraint implements ConstraintInterface
{
    private const UUID_REGEX = '/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/i';

    public function metadata(): ConstraintMetaWithPayload
    {
        return ConstraintMetaWithPayload::of(
            name: 'UUID',
            payload: [
                'message' => 'Value must be valid uuid string',
            ],
        );
    }

    public function check(Context $context, mixed $value): iterable
    {
        if (1 === preg_match(self::UUID_REGEX, $value)) {
            return;
        }

        yield invalid($context);
    }
}
