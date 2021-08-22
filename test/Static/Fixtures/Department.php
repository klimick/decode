<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

/**
 * @psalm-immutable
 */
final class Department
{
    /**
     * @param list<Department> $subDepartments
     */
    public function __construct(
        public string $name,
        public array $subDepartments,
    ) {}
}
