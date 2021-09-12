<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\Boolean;

use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\anyOf;
use function Klimick\Decode\Constraint\greater;
use function Klimick\Decode\Constraint\equal;
use function Klimick\Decode\Constraint\less;

final class AnyOfTest extends TestCase
{
    public function testValid(): void
    {
        Check::isValid()
            ->forConstraint(
                anyOf(
                    greater(than: 10),
                    less(than: 15),
                )
            )
            ->withValue(15);
    }

    public function testInvalid(): void
    {
        Check::isInvalid()
            ->forConstraint(
                anyOf(
                    equal(to: 10),
                    less(than: 15),
                )
            )
            ->withValue(20);
    }
}
