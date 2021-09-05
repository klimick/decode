<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\DecodeResultHandler;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Report\UndefinedErrorReport;
use Klimick\Decode\Report\UnionCaseReport;
use Klimick\Decode\Test\Static\Fixtures\Person;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\maxLength;
use function Klimick\Decode\Constraint\minLength;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\literal;
use function Klimick\Decode\Decoder\nonEmptyString;
use function Klimick\Decode\Decoder\null;
use function Klimick\Decode\Decoder\object;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\union;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;

final class DefaultReporterTest extends TestCase
{
    public function testReport(): void
    {
        $decoder = shape(
            name: string(),
            age: int(),
            is_approved: literal(true, false),
            application_ver: literal('v1', 'v2'),
            admin: object(Person::class)(
                name: string(),
                age: int(),
            ),
            privilege_id: union(int(), null()),
            address: shape(
                postcode: string()->constrained(minLength(is: 6), maxLength(is: 6)),
                city: nonEmptyString(),
            )
        );

        $data = [
            'name' => 42,
            'age' => 'foo',
            'application_ver' => 'v3',
            'privilege_id' => '100',
            'admin' => null,
            'address' => [
                'postcode' => '12345',
                'city' => '',
            ]
        ];

        $errorReport = DecodeResultHandler::handle(
            value: decode($data, $decoder),
            useShortClassNames: true
        );

        assertInstanceOf(ErrorReport::class, $errorReport);

        assertEquals(
            [
                new TypeErrorReport('$.name', 'string', 42),
                new TypeErrorReport('$.age', 'int', "'foo'"),
                new TypeErrorReport('$.application_ver', "'v1' | 'v2'", "'v3'"),
                new TypeErrorReport('$.admin', 'Person', null),
                new TypeErrorReport('$.address.city', 'non-empty-string', "''"),
            ],
            $errorReport->typeErrors
        );

        assertEquals(
            [
                new UndefinedErrorReport('$', 'is_approved'),
            ],
            $errorReport->undefinedErrors,
        );

        assertEquals(
            [
                new ConstraintErrorReport('$.address.postcode', 'MIN_LENGTH', "'12345'", ['expected' => 6, 'actual' => 5])
            ],
            $errorReport->constraintErrors,
        );

        assertEquals(
            [
                new UnionCaseReport('int', new ErrorReport(
                    typeErrors: [
                        new TypeErrorReport('$.privilege_id', 'int', "'100'"),
                    ],
                )),
                new UnionCaseReport('null', new ErrorReport(
                    typeErrors: [
                        new TypeErrorReport('$.privilege_id', 'null', "'100'"),
                    ],
                )),
            ],
            $errorReport->unionTypeErrors,
        );
    }
}
