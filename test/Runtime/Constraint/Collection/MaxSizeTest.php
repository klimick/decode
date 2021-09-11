<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\Collection;

use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\maxSize;

final class MaxSizeTest extends TestCase
{
    public function testValid(): void
    {
        Check::isValid()
            ->forConstraint(maxSize(is: 5))
            ->withValue([1, 2, 3, 4, 5]);
    }

    public function testIsInvalid(): void
    {
        Check::isInvalid()
            ->forConstraint(maxSize(is: 5))
            ->withValue([1, 2, 3, 4, 5, 6]);
    }
}
