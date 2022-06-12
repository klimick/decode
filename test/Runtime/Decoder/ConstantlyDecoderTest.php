<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\constantly;

final class ConstantlyDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('constant<42>', constantly(42));
        Assert::name('constant<1.42>', constantly(1.42));
        Assert::name("constant<'str'>", constantly('str'));
        Assert::name('constant<true>', constantly(true));
        Assert::name('constant<false>', constantly(false));
        Assert::name('constant<null>', constantly(null));
        Assert::name(
            "constant<array{42, 1.42, 'str', true, false, null}>",
            constantly([42, 1.42, 'str', true, false, null]),
        );
        Assert::name(
            "constant<array{0: true, 1: false, 2: null, a: 42, b: 1.42, c: 'str'}>",
            constantly([true, false, null, 'a' => 42, 'b' => 1.42, 'c' => 'str']),
        );
    }

    public function testDecodeSuccess(): void
    {
        $constant = 42;
        $decoder = constantly($constant);

        Assert::decodeSuccess($constant, decode(1, $decoder));
        Assert::decodeSuccess($constant, decode(1.42, $decoder));
        Assert::decodeSuccess($constant, decode(true, $decoder));
        Assert::decodeSuccess($constant, decode(false, $decoder));
        Assert::decodeSuccess($constant, decode(['1', '2', '3'], $decoder));
    }
}
