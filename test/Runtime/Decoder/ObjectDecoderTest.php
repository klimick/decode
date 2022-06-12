<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Report\UndefinedErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use Klimick\Decode\Test\Static\Fixtures\Person;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\object;
use function Klimick\Decode\Decoder\string;

final class ObjectDecoderTest extends TestCase
{
    /**
     * @return DecoderInterface<Person>
     * @psalm-pure
     */
    private static function getDecoder(): DecoderInterface
    {
        return object(Person::class)(
            name: string(),
            age: int(),
        );
    }

    public function testTypename(): void
    {
        Assert::name(Person::class, self::getDecoder());
    }

    public function testDecodeFailed(): void
    {
        Assert::decodeFailed(
            expectedReport: new ErrorReport(
                undefinedErrors: [
                    new UndefinedErrorReport('$.name'),
                    new UndefinedErrorReport('$.age'),
                ],
            ),
            actualDecoded: decode([], self::getDecoder()),
        );
        Assert::decodeFailed(
            expectedReport: new ErrorReport(
                typeErrors: [
                    new TypeErrorReport('$.name', 'string', ['test']),
                ],
                undefinedErrors: [
                    new UndefinedErrorReport('$.age'),
                ],
            ),
            actualDecoded: decode(['name' => ['test']], self::getDecoder()),
        );
        Assert::decodeFailed(
            expectedReport: new ErrorReport(
                typeErrors: [
                    new TypeErrorReport('$.name', 'string', ['test']),
                    new TypeErrorReport('$.age', 'int', 'invalid'),
                ],
            ),
            actualDecoded: decode(['name' => ['test'], 'age' => 'invalid'], self::getDecoder()),
        );
    }

    public function testDecodeSuccess(): void
    {
        Assert::decodeSuccess(
            expectedValue: new Person(name: 'test', age: 42),
            actualDecoded: decode(['name' => 'test', 'age' => 42], self::getDecoder()),
        );
    }
}
