<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Collector;

use Klimick\PsalmTest\Integration\Assertion\AssertionData;
use Psalm\CodeLocation;

/**
 * @psalm-immutable
 */
final class SeePsalmIssuesData implements AssertionData
{
    /**
     * @param list<SeePsalmIssue> $issues
     */
    public function __construct(public CodeLocation $code_location, public array $issues = [])
    {
    }

    public static function empty(CodeLocation $code_location): self
    {
        return new self($code_location);
    }

    /**
     * @no-named-arguments
     */
    public function concat(SeePsalmIssue ...$issues): self
    {
        $self = clone $this;
        $self->issues = [...$self->issues, ...$issues];

        return $self;
    }
}
