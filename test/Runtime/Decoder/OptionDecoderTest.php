<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\null;
use function Klimick\Decode\Decoder\option;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\union;

final class OptionDecoderTest extends TestCase
{
    /**
     * @return DecoderInterface<Option<string>>
     */
    private static function getDecoder(): DecoderInterface
    {
        return option(string());
    }

    public function testTypename(): void
    {
        Assert::name('Option<string>', self::getDecoder());
    }

    public function testDecodeFailed(): void
    {
        $decoder = self::getDecoder();
        $value = 1;

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), $value),
            ]),
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeToSome(): void
    {
        $strDecoder = option(string());
        $strOrNullDecoder = option(union(string(), null()));

        Assert::decodeSuccess(
            expectedValue: Option::some('str'),
            actualDecoded: decode('str', $strDecoder),
        );
        Assert::decodeSuccess(
            expectedValue: Option::none(),
            actualDecoded: decode(null, $strDecoder),
        );
        Assert::decodeSuccess(
            expectedValue: Option::some(null),
            actualDecoded: decode(null, $strOrNullDecoder),
        );
        Assert::decodeSuccess(
            expectedValue: ['value' => Option::some('str')],
            actualDecoded: decode(['value' => 'str'], shape(value: $strDecoder)),
        );
        Assert::decodeSuccess(
            expectedValue: ['value' => Option::none()],
            actualDecoded: decode([], shape(value: $strDecoder)),
        );
        Assert::decodeSuccess(
            expectedValue: ['value' => Option::none()],
            actualDecoded: decode(['value' => null], shape(value: $strDecoder)),
        );
        Assert::decodeSuccess(
            expectedValue: ['value' => Option::some(null)],
            actualDecoded: decode(['value' => null], shape(value: $strOrNullDecoder)),
        );
    }
}
