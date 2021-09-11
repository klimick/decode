<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\Collection;

use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\exists;
use function Klimick\Decode\Constraint\greater;

final class ExistsTest extends TestCase
{
    public function testValid(): void
    {
        Check::isValid()
            ->forConstraint(exists(greater(than: 5)))
            ->withValue([1, 2, 3, 4, 5, 6]);
    }

    public function testInvalid(): void
    {
        Check::isInvalid()
            ->forConstraint(exists(greater(than: 5)))
            ->withValue([1, 2, 3, 4, 5]);
    }
}
