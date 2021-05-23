<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

/**
 * @psalm-immutable
 */
final class ErrorReport
{
    /**
     * @param list<TypeErrorReport> $typeErrors
     * @param list<ConstraintErrorReport> $constraintErrors
     * @param list<string> $undefinedErrors
     */
    public function __construct(
        public array $typeErrors,
        public array $constraintErrors,
        public array $undefinedErrors,
    ) { }
}
