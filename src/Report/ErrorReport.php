<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

/**
 * @psalm-immutable
 */
final class ErrorReport
{
    /**
     * @param list<TypeError> $typeErrors
     * @param list<string> $undefinedProperties
     */
    public function __construct(
        public array $typeErrors,
        public array $undefinedProperties,
    ) { }
}
