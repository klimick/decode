<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\Helper;

final class Check
{
    public static function isValid(): GimmeConstraint
    {
        return new GimmeConstraint(isValid: true);
    }

    public static function isInvalid(): GimmeConstraint
    {
        return new GimmeConstraint(isValid: false);
    }
}
