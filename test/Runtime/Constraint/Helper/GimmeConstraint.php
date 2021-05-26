<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\Helper;

use Klimick\Decode\Constraint\ConstraintInterface;

final class GimmeConstraint
{
    public function __construct(public bool $isValid) { }

    public function forConstraint(ConstraintInterface $constraint): RunCheck
    {
        return new RunCheck($this->isValid, $constraint);
    }
}
