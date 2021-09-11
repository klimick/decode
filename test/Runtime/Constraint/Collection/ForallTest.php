<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\Collection;

use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\forall;
use function Klimick\Decode\Constraint\greater;

final class ForallTest extends TestCase
{
    public function testValid(): void
    {
        Check::isValid()
            ->forConstraint(forall(greater(than: 5)))
            ->withValue([6, 7, 8, 9]);
    }

    public function testInvalid(): void
    {
        Check::isInvalid()
            ->forConstraint(forall(greater(than: 5)))
            ->withValue([5, 6, 7, 8, 9]);
    }
}
