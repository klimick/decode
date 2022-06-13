<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Report\UndefinedErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use Klimick\Decode\Test\Static\Fixtures\PartialPerson;
use Klimick\Decode\Test\Static\Fixtures\Person;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\null;
use function Klimick\Decode\Decoder\object;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\tagged;
use function Klimick\Decode\Decoder\union;

final class TaggedUnionDecoderTest extends TestCase
{
    /**
     * @return DecoderInterface<Person|PartialPerson>
     */
    private static function getDecoder(): DecoderInterface
    {
        return tagged(with: 'type')(
            full: object(Person::class)(
                name: string(),
                age: int(),
            ),
            partial: object(PartialPerson::class)(
                maybeName: union(string(), null()),
                maybeAge: union(int(), null()),
            ),
        );
    }

    public function testTypename(): void
    {
        Assert::name(Person::class . ' | ' . PartialPerson::class, self::getDecoder());
    }

    public function testDecodeFailed(): void
    {
        Assert::decodeFailed(
            expected: new ErrorReport([
                new UndefinedErrorReport('$.type'),
            ]),
            actual: decode(null, self::getDecoder()),
        );

        Assert::decodeFailed(
            expected: new ErrorReport([
                new UndefinedErrorReport('$.name'),
                new UndefinedErrorReport('$.age'),
            ]),
            actual: decode(
                value: ['type' => 'full'],
                with: self::getDecoder(),
            ),
        );

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$.name', 'string', 42),
                new UndefinedErrorReport('$.age'),
            ]),
            actual: decode(
                value: ['type' => 'full', 'name' => 42],
                with: self::getDecoder(),
            ),
        );

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$.name', 'string', 42),
                new TypeErrorReport('$.age', 'int', 'test'),
            ]),
            actual: decode(
                value: ['type' => 'full', 'name' => 42, 'age' => 'test'],
                with: self::getDecoder(),
            ),
        );

        Assert::decodeFailed(
            expected: new ErrorReport([
                new UndefinedErrorReport('$.maybeName'),
                new UndefinedErrorReport('$.maybeAge'),
            ]),
            actual: decode(
                value: ['type' => 'partial'],
                with: self::getDecoder(),
            ),
        );
    }

    public function testDecodeSuccess(): void
    {
        Assert::decodeSuccess(
            expectedValue: new Person(name: 'test', age: 42),
            actualDecoded: decode(['type' => 'full', 'name' => 'test', 'age' => 42], self::getDecoder()),
        );
        Assert::decodeSuccess(
            expectedValue: new PartialPerson(maybeName: null, maybeAge: null),
            actualDecoded: decode(['type' => 'partial', 'maybeName' => null, 'maybeAge' => null], self::getDecoder()),
        );
        Assert::decodeSuccess(
            expectedValue: new PartialPerson(maybeName: 'test', maybeAge: null),
            actualDecoded: decode(['type' => 'partial', 'maybeName' => 'test', 'maybeAge' => null], self::getDecoder()),
        );
        Assert::decodeSuccess(
            expectedValue: new PartialPerson(maybeName: null, maybeAge: 42),
            actualDecoded: decode(['type' => 'partial', 'maybeName' => null, 'maybeAge' => 42], self::getDecoder()),
        );
        Assert::decodeSuccess(
            expectedValue: new PartialPerson(maybeName: 'test', maybeAge: 42),
            actualDecoded: decode(['type' => 'partial', 'maybeName' => 'test', 'maybeAge' => 42], self::getDecoder()),
        );
    }
}
