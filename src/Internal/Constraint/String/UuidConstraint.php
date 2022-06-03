<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\String;

use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\invalid;

/**
 * @implements ConstraintInterface<string>
 * @psalm-immutable
 */
final class UuidConstraint implements ConstraintInterface
{
    private const UUID_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    public function name(): string
    {
        return 'UUID';
    }

    public function payload(): array
    {
        return [
            'message' => 'Value must be valid uuid string',
        ];
    }

    public function check(Context $context, mixed $value): iterable
    {
        if (1 === preg_match(self::UUID_REGEX, $value)) {
            return;
        }

        yield invalid($context, $this);
    }
}
