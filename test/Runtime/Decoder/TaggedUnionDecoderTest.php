<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Test\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\tagged;

final class TaggedUnionDecoderTest extends TestCase
{
    public function testFailWhenUntrustedValueIsNotArray(): void
    {
        $decoder = tagged(with: 'type')(
            type1: shape(foo: string(), bar: int()),
            type2: shape(id: int(), num: int()),
        );
        $value = 10;

        Check::thatInvalidFor($decoder)($value);
    }

    public function testFailWhenTagValueIsMissing(): void
    {
        $decoder = tagged(with: 'type')(
            type1: shape(foo: string(), bar: int()),
            type2: shape(id: int(), num: int()),
        );
        $value = ['foo' => '_', 'bar' => 0];

        Check::thatInvalidFor($decoder)($value);
    }

    public function testFailWhenIncomingTagIsNotString(): void
    {
        $decoder = tagged(with: 'type')(
            type1: shape(foo: string(), bar: int()),
            type2: shape(id: int(), num: int()),
        );
        $value = ['foo' => '_', 'bar' => 0, 'type' => 0];

        Check::thatInvalidFor($decoder)($value);
    }

    public function testFailWhenIncomingTagIsUnknown(): void
    {
        $decoder = tagged(with: 'type')(
            type1: shape(foo: string(), bar: int()),
            type2: shape(id: int(), num: int()),
        );
        $value = ['foo' => '_', 'bar' => 0, 'type' => 'type3'];

        Check::thatInvalidFor($decoder)($value);
    }

    public function testValidValue(): void
    {
        $decoder = tagged(with: 'type')(
            type1: shape(foo: string(), bar: int()),
            type2: shape(id: int(), num: int()),
        );

        Check::thatValidFor($decoder)(['foo' => '_', 'bar' => 0, 'type' => 'type1']);
        Check::thatValidFor($decoder)(['id' => 0, 'num' => 0, 'type' => 'type2']);
    }
}
