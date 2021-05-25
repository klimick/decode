<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

final class Invalid
{
    /**
     * @param non-empty-list<ConstraintError> $errors
     */
    public function __construct(public array $errors) { }
}
