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
final class MatchesRegexConstraint implements ConstraintInterface
{
    public function __construct(public string $regex) { }

    public function name(): string
    {
        return 'MATCHES_REGEX';
    }

    public function payload(): array
    {
        return ['mustMatchesTo' => $this->regex];
    }

    public function check(Context $context, mixed $value): iterable
    {
        if (1 === preg_match($this->regex, $value)) {
            return;
        }

        yield invalid($context, $this);
    }
}
