<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

/**
 * @psalm-immutable
 */
final class UnionCaseReport
{
    public function __construct(
        public string $case,
        public ErrorReport $errors,
    ) {}
}
