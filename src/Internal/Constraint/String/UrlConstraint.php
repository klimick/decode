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
final class UrlConstraint implements ConstraintInterface
{
    public function name(): string
    {
        return 'URL';
    }

    public function check(Context $context, mixed $value): Either
    {
        return false === filter_var($value, FILTER_SANITIZE_URL)
            ? invalid($context, $this)
            : valid();
    }
}
