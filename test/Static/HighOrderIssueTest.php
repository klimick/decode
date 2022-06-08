<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use Fp\PsalmToolkit\StaticTest\PsalmTest;
use Fp\PsalmToolkit\StaticTest\StaticTestCase;
use function Klimick\Decode\Constraint\greater;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;

final class HighOrderIssueTest extends PsalmTest
{
    public function __invoke(): void
    {
        StaticTestCase::describe('Invalid property alias issue')
            ->haveCode(fn() => shape(
                prop: int()->from('person_age'),
            ))
            ->seePsalmIssue(
                type: 'InvalidPropertyAlias',
                message: 'Invalid argument for DecoderInterface::from. ' .
                'Argument must be non-empty-string literal with "$." prefix or just "$"'
            );

        StaticTestCase::describe('Incompatible constraint issue')
            ->haveCode(
                fn() => string()->constrained(greater(than: 10))
            )
            ->seePsalmIssue(
                type: 'IncompatibleConstraint',
                message: 'Value of type string cannot be checked with constraints of type numeric',
            );
    }
}
