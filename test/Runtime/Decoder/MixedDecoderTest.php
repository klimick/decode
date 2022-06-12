<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\mixed;

final class MixedDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('mixed', mixed());
    }

    public function testDecodeSuccess(): void
    {
        $decoder = mixed();

        Assert::decodeSuccess(1, decode(1, $decoder));
        Assert::decodeSuccess(1.42, decode(1.42, $decoder));
        Assert::decodeSuccess(true, decode(true, $decoder));
        Assert::decodeSuccess(false, decode(false, $decoder));
        Assert::decodeSuccess(['1', '2', '3'], decode(['1', '2', '3'], $decoder));
    }
}
