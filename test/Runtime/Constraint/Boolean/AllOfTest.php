<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\Boolean;

use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\allOf;
use function Klimick\Decode\Constraint\greater;
use function Klimick\Decode\Constraint\less;

final class AllOfTest extends TestCase
{
    public function testValid(): void
    {
        Check::isValid()
            ->forConstraint(
                allOf(
                    greater(than: 10),
                    less(than: 20),
                )
            )
            ->withValue(15);
    }

    public function testInvalid(): void
    {
        Check::isInvalid()
            ->forConstraint(
                allOf(
                    greater(than: 10),
                    less(than: 20),
                )
            )
            ->withValue(10);
    }
}
