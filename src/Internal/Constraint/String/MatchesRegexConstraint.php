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
final class MatchesRegexConstraint implements ConstraintInterface
{
    public function __construct(public string $regex) { }

    public function name(): string
    {
        return 'MATCHES_REGEX';
    }

    public function check(Context $context, mixed $value): Either
    {
        return 1 !== preg_match($this->regex, $value, $m)
            ? invalid($context, $this, ['expected' => $this->regex])
            : valid();
    }
}
