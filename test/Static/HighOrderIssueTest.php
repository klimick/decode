<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use Klimick\Decode\Decoder\DecoderInterface;
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
        StaticTestCase::describe('Optional call contradiction issue')
            ->haveCode(fn() => shape(
                prop: int()->default(42)->optional(),
            ))
            ->seePsalmIssue(
                type: 'OptionalCallContradictionIssue',
                message: 'Using DecoderInterface::default and DecoderInterface::optional at the same time has no sense.',
            );

        StaticTestCase::describe('Invalid property alias issue')
            ->haveCode(fn() => shape(
                prop: int()->from('person_age'),
            ))
            ->seePsalmIssue(
                type: 'InvalidPropertyAliasIssue',
                message: 'Invalid argument for DecoderInterface::from. ' .
                'Argument must be non-empty-string literal with "$." prefix or just "$"'
            );

        StaticTestCase::describe('Brand already defined issue for optional call')
            ->haveCode(fn() => shape(
                prop: string()->optional()->optional(),
            ))
            ->seePsalmIssue(
                type: 'BrandAlreadyDefinedIssue',
                message: 'Method #[decoder]::optional should not called multiple times.',
                args: [
                    'decoder' => DecoderInterface::class,
                ],
            );

        StaticTestCase::describe('Brand already defined issue for from call')
            ->haveCode(fn() => shape(
                prop: string()->from('$.another_prop')->from('$.another_prop'),
            ))
            ->seePsalmIssue(
                type: 'BrandAlreadyDefinedIssue',
                message: 'Method #[decoder]::from should not called multiple times.',
                args: [
                    'decoder' => DecoderInterface::class,
                ],
            );

        StaticTestCase::describe('Incompatible constraint issue')
            ->haveCode(
                fn() => string()->constrained(greater(than: 10))
            )
            ->seePsalmIssue(
                type: 'IncompatibleConstraintIssue',
                message: 'Value of type string cannot be checked with constraints of type numeric',
            );
    }
}
