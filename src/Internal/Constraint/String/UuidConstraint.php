<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\String;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\invalid;
use function Klimick\Decode\Constraint\valid;

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
        return [];
    }

    public function check(Context $context, mixed $value): Either
    {
        return 1 !== preg_match(self::UUID_REGEX, $value)
            ? invalid($context, $this)
            : valid();
    }
}
