<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\Collection;

use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\inCollection;

final class InCollectionTest extends TestCase
{
    public function testValid(): void
    {
        Check::isValid()
            ->forConstraint(inCollection(10))
            ->withValue([10, 11, 12, 13, 14, 15]);
    }

    public function testInvalid(): void
    {
        Check::isInvalid()
            ->forConstraint(inCollection(10))
            ->withValue([11, 12, 13, 14, 15, 16]);
    }
}
