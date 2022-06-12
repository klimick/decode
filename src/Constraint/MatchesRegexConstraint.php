<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Constraint\Metadata\ConstraintMetaWithPayload;
use Klimick\Decode\Context;

/**
 * @implements ConstraintInterface<string>
 * @psalm-immutable
 */
final class MatchesRegexConstraint implements ConstraintInterface
{
    public function __construct(
        public string $regex,
    ) {}

    public function metadata(): ConstraintMetaWithPayload
    {
        return ConstraintMetaWithPayload::of(
            name: 'MATCHES_REGEX',
            payload: [
                'mustMatchesTo' => $this->regex,
            ],
        );
    }

    public function check(Context $context, mixed $value): iterable
    {
        if (1 === preg_match($this->regex, $value)) {
            return;
        }

        yield invalid($context);
    }
}
