<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Helper\Predicate;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\nonEmptyString;
use function Klimick\Decode\Test\Helper\forAll;

final class NonEmptyStringDecoderTest extends TestCase
{
    public function testValidForAllNonEmptyStrings(): void
    {
        forAll(Gen::nonEmptyString())->then(Check::thatValidFor(nonEmptyString()));
    }

    public function testInvalidForAllNotNonEmptyStrings(): void
    {
        forAll(Gen::scalar())
            ->when(fn(mixed $value) => !Predicate::isNonEmptyString($value))
            ->then(Check::thatInvalidFor(nonEmptyString()));
    }
}
