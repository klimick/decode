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
final class TrimmedConstraint implements ConstraintInterface
{
    public function name(): string
    {
        return 'TRIMMED';
    }

    public function payload(): array
    {
        return [
            'message' => 'Value must not contain leading or trailing whitespaces',
        ];
    }

    public function check(Context $context, mixed $value): Either
    {
        return trim($value) !== $value
            ? invalid($context, $this)
            : valid();
    }
}
