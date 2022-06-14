<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use Klimick\Decode\Test\Static\Fixtures\Department;
use Klimick\Decode\Test\Static\Fixtures\Person;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\instance;

final class InstanceofDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name(Person::class, instance(of: Person::class));
    }

    public function testDecodeFailedWithUnexpectedValue(): void
    {
        $decoder = instance(of: Person::class);
        $actual = new Department(name: 'test', subDepartments: []);

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), $actual),
            ]),
            actual: decode($actual, $decoder),
        );
    }

    public function testDecodeSuccessWithValueOfExpectedType(): void
    {
        $decoder = instance(of: Person::class);
        $person = new Person(name: 'test', age: 42);

        Assert::decodeSuccess(
            expectedValue: $person,
            actualDecoded: decode($person, $decoder),
        );
    }
}
