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
     * @param list<array<string, ErrorReport>> $unionTypeErrors
     * @param list<ConstraintErrorReport> $constraintErrors
     * @param list<UndefinedErrorReport> $undefinedErrors
     */
    public function __construct(
        public array $typeErrors,
        public array $unionTypeErrors,
        public array $constraintErrors,
        public array $undefinedErrors,
    ) { }
}
